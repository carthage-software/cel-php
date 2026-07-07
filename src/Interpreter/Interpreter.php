<?php

declare(strict_types=1);

namespace Cel\Interpreter;

use Cel\Environment\EnvironmentInterface;
use Cel\Exception\EvaluationException;
use Cel\Exception\InvalidConditionTypeException;
use Cel\Exception\InvalidOptionalConstructionException;
use Cel\Exception\MessageConstructionException;
use Cel\Exception\NoSuchFunctionException;
use Cel\Exception\NoSuchKeyException;
use Cel\Exception\NoSuchOverloadException;
use Cel\Exception\NoSuchTypeException;
use Cel\Exception\NoSuchVariableException;
use Cel\Exception\UnexpectedMapKeyTypeException;
use Cel\Exception\UnsupportedOperationException;
use Cel\Interpreter\Macro\MacroContextInterface;
use Cel\Interpreter\Macro\MacroRegistry;
use Cel\Runtime\Configuration;
use Cel\Runtime\OperationRegistry;
use Cel\Span\Span;
use Cel\Syntax\Aggregate\ListExpression;
use Cel\Syntax\Aggregate\MapExpression;
use Cel\Syntax\Aggregate\MessageExpression;
use Cel\Syntax\Binary\BinaryExpression;
use Cel\Syntax\Binary\BinaryOperatorKind;
use Cel\Syntax\ConditionalExpression;
use Cel\Syntax\Expression;
use Cel\Syntax\Literal\BoolLiteralExpression;
use Cel\Syntax\Literal\BytesLiteralExpression;
use Cel\Syntax\Literal\FloatLiteralExpression;
use Cel\Syntax\Literal\IntegerLiteralExpression;
use Cel\Syntax\Literal\LiteralExpression;
use Cel\Syntax\Literal\NullLiteralExpression;
use Cel\Syntax\Literal\StringLiteralExpression;
use Cel\Syntax\Literal\UnsignedIntegerLiteralExpression;
use Cel\Syntax\Member\CallExpression;
use Cel\Syntax\Member\IdentifierExpression;
use Cel\Syntax\Member\IndexExpression;
use Cel\Syntax\Member\MemberAccessExpression;
use Cel\Syntax\ParenthesizedExpression;
use Cel\Syntax\Unary\UnaryExpression;
use Cel\Util\MapKeyUtil;
use Cel\Value\BooleanValue;
use Cel\Value\BytesValue;
use Cel\Value\FloatValue;
use Cel\Value\IntegerValue;
use Cel\Value\ListValue;
use Cel\Value\MapValue;
use Cel\Value\MessageValue;
use Cel\Value\NullValue;
use Cel\Value\OptionalValue;
use Cel\Value\StringValue;
use Cel\Value\TypeValue;
use Cel\Value\UnsignedIntegerValue;
use Cel\Value\Value;
use Cel\Value\ValueKind;
use Cel\Value\WellKnownType;
use Override;
use Throwable;

use function array_key_exists;
use function array_map;
use function array_reverse;
use function array_slice;
use function count;
use function implode;
use function in_array;
use function sprintf;
use function str_starts_with;
use function strcasecmp;

/**
 * A tree-walking interpreter that evaluates expressions by recursively
 * traversing the expression tree.
 *
 * @mago-expect lint:kan-defect
 * @mago-expect lint:cyclomatic-complexity
 *
 * @api
 */
final class Interpreter implements InterpreterInterface, MacroContextInterface
{
    private bool $idempotent = true;
    private readonly MacroRegistry $macroRegistry;

    /**
     * The environment as it stood when evaluation began, used to resolve
     * absolute (leading-dot) references regardless of comprehension scoping.
     */
    private readonly EnvironmentInterface $rootEnvironment;

    public function __construct(
        private readonly Configuration $configuration,
        private readonly OperationRegistry $registry,
        private EnvironmentInterface $environment,
    ) {
        $this->macroRegistry = $configuration->getMacroRegistry();
        $this->rootEnvironment = $environment;
    }

    /**
     * @inheritDoc
     */
    #[Override]
    public function getEnvironment(): EnvironmentInterface
    {
        return $this->environment;
    }

    /**
     * @inheritDoc
     */
    #[Override]
    public function reset(): void
    {
        $this->idempotent = true;
    }

    /**
     * @inheritDoc
     */
    #[Override]
    public function wasIdempotent(): bool
    {
        return $this->idempotent;
    }

    /**
     * @inheritDoc
     */
    #[Override]
    public function evaluate(Expression $expression): Value
    {
        return $this->run($expression);
    }

    /**
     * @inheritDoc
     */
    #[Override]
    public function withEnvironment(EnvironmentInterface $environment, callable $callback): mixed
    {
        $previousEnvironment = $this->environment;
        $this->environment = $environment;

        try {
            return $callback();
        } finally {
            $this->environment = $previousEnvironment;
        }
    }

    /**
     * @inheritDoc
     *
     * @throws EvaluationException If evaluation fails.
     * @throws UnsupportedOperationException If an unsupported operation is encountered.
     */
    #[Override]
    public function run(Expression $expression): Value
    {
        return match ($expression::class) {
            ParenthesizedExpression::class => $this->run($expression->expression),
            ListExpression::class => $this->list($expression),
            MapExpression::class => $this->map($expression),
            UnaryExpression::class => $this->unary($expression),
            BinaryExpression::class => $this->binary($expression),
            ConditionalExpression::class => $this->conditional($expression),
            MemberAccessExpression::class => $this->memberAccess($expression),
            IndexExpression::class => $this->index($expression),
            IdentifierExpression::class => $this->identifier($expression),
            CallExpression::class => $this->call($expression),
            MessageExpression::class => $this->message($expression),
            BoolLiteralExpression::class => new BooleanValue($expression->value),
            BytesLiteralExpression::class => new BytesValue($expression->value),
            FloatLiteralExpression::class => new FloatValue($expression->value),
            IntegerLiteralExpression::class => new IntegerValue($expression->value),
            NullLiteralExpression::class => new NullValue(),
            StringLiteralExpression::class => new StringValue($expression->value),
            UnsignedIntegerLiteralExpression::class => new UnsignedIntegerValue($expression->value),
            default => $expression instanceof LiteralExpression
                ? throw new UnsupportedOperationException(
                    sprintf('Unsupported literal of type `%s`', $expression::class),
                    $expression->getSpan(),
                )
                : throw new UnsupportedOperationException(
                    sprintf('Unsupported expression of type `%s`', $expression::class),
                    $expression->getSpan(),
                ),
        };
    }

    /**
     * @throws EvaluationException
     */
    private function list(ListExpression $expression): Value
    {
        $values = [];
        foreach ($expression->elements->elements as $element) {
            $value = $this->run($element->value);

            if ($element->isOptional()) {
                if (!$value instanceof OptionalValue) {
                    throw new InvalidOptionalConstructionException(
                        sprintf('Optional list element requires an optional value, got `%s`', $value->getType()),
                        $element->getSpan(),
                    );
                }

                if (null !== $value->value) {
                    $values[] = $value->value;
                }

                continue;
            }

            $values[] = $value;
        }

        return new ListValue($values);
    }

    /**
     * @throws EvaluationException
     */
    private function map(MapExpression $expression): Value
    {
        $values = [];
        foreach ($expression->entries as $entry) {
            $key = $this->run($entry->key);
            if (
                !$key instanceof StringValue
                && !$key instanceof IntegerValue
                && !$key instanceof UnsignedIntegerValue
                && !$key instanceof BooleanValue
            ) {
                throw new UnexpectedMapKeyTypeException(
                    sprintf('Map keys must be bool, int, uint, or string, got `%s`', $key->getType()),
                    $entry->key->getSpan(),
                );
            }

            $mapKey = MapKeyUtil::resolve($key);
            if (null === $mapKey) {
                // Unreachable: the key type was validated above.
                throw new UnexpectedMapKeyTypeException(
                    sprintf('Map keys must be bool, int, uint, or string, got `%s`', $key->getType()),
                    $entry->key->getSpan(),
                );
            }

            $value = $this->run($entry->value);

            if ($entry->isOptional()) {
                if (!$value instanceof OptionalValue) {
                    throw new InvalidOptionalConstructionException(
                        sprintf('Optional map entry requires an optional value, got `%s`', $value->getType()),
                        $entry->value->getSpan(),
                    );
                }

                if (null !== $value->value) {
                    $values[$mapKey] = $value->value;
                }

                continue;
            }

            $values[$mapKey] = $value;
        }

        return new MapValue($values);
    }

    /**
     * @throws EvaluationException
     */
    private function unary(UnaryExpression $expression): Value
    {
        $operand = $this->run($expression->operand);

        $handler = $this->registry->getUnaryOperator($expression->operator->kind, $operand->getKind());
        if (null === $handler) {
            throw new NoSuchOverloadException(
                sprintf('No such overload for %s`%s`', $expression->operator->kind->getSymbol(), $operand->getType()),
                $expression->getSpan(),
            );
        }

        return $handler($expression, $operand);
    }

    /**
     * @throws EvaluationException
     *
     * @mago-expect lint:halstead
     */
    private function binary(BinaryExpression $expression): Value
    {
        $operator = $expression->operator->kind;

        // Handle short-circuit evaluation for AND with literal booleans
        if ($operator === BinaryOperatorKind::And) {
            if ($expression->left instanceof BoolLiteralExpression && !$expression->left->value) {
                return new BooleanValue(false);
            }

            if ($expression->right instanceof BoolLiteralExpression && !$expression->right->value) {
                return new BooleanValue(false);
            }

            // Evaluate left operand
            $left = $this->run($expression->left);

            // If left is boolean and false, short-circuit without evaluating right
            if ($left instanceof BooleanValue && !$left->value) {
                return new BooleanValue(false);
            }

            // Evaluate right operand
            $right = $this->run($expression->right);

            // Try to get handler from registry
            $handler = $this->registry->getBinaryOperator($operator, $left->getKind(), $right->getKind());
            if (null !== $handler) {
                return $handler($expression, $left, $right);
            }

            // Fallback error for AND
            throw new NoSuchOverloadException(
                sprintf(
                    'No such overload for `%s` %s `%s`',
                    $left->getType(),
                    $operator->getSymbol(),
                    $right->getType(),
                ),
                $expression->left->getSpan()->join($expression->right->getSpan()),
            );
        }

        // Handle short-circuit evaluation for OR with literal booleans
        if ($operator === BinaryOperatorKind::Or) {
            if ($expression->left instanceof BoolLiteralExpression && $expression->left->value) {
                return new BooleanValue(true);
            }

            if ($expression->right instanceof BoolLiteralExpression && $expression->right->value) {
                return new BooleanValue(true);
            }

            // Evaluate left operand
            $left = $this->run($expression->left);

            // If left is boolean and true, short-circuit without evaluating right
            if ($left instanceof BooleanValue && $left->value) {
                return new BooleanValue(true);
            }

            // Evaluate right operand
            $right = $this->run($expression->right);

            // Try to get handler from registry
            $handler = $this->registry->getBinaryOperator($operator, $left->getKind(), $right->getKind());
            if (null !== $handler) {
                return $handler($expression, $left, $right);
            }

            // Fallback error for OR
            throw new NoSuchOverloadException(
                sprintf(
                    'No such overload for `%s` %s `%s`',
                    $left->getType(),
                    $operator->getSymbol(),
                    $right->getType(),
                ),
                $expression->left->getSpan()->join($expression->right->getSpan()),
            );
        }

        // For all other operators, evaluate both operands and use the registry
        $left = $this->run($expression->left);
        $right = $this->run($expression->right);

        $handler = $this->registry->getBinaryOperator($operator, $left->getKind(), $right->getKind());
        if (null === $handler) {
            throw new NoSuchOverloadException(
                sprintf(
                    'No such overload for `%s` %s `%s`',
                    $left->getType(),
                    $operator->getSymbol(),
                    $right->getType(),
                ),
                $expression->left->getSpan()->join($expression->right->getSpan()),
            );
        }

        return $handler($expression, $left, $right);
    }

    /**
     * @throws EvaluationException
     */
    private function conditional(ConditionalExpression $expression): Value
    {
        $condition = $this->run($expression->condition);
        if (!$condition instanceof BooleanValue) {
            throw new InvalidConditionTypeException(
                sprintf('Condition must be boolean, got `%s`', $condition->getType()),
                $expression->condition->getSpan(),
            );
        }

        return $condition->value ? $this->run($expression->then) : $this->run($expression->else);
    }

    /**
     * @throws EvaluationException
     */
    private function memberAccess(MemberAccessExpression $expression): Value
    {
        // A dotted chain of plain identifiers may name a qualified variable or
        // type (e.g. `x.y` or `google.protobuf.Timestamp`, or an absolute `.y`
        // reference). Resolve those before falling back to field selection.
        $qualified = $this->resolveQualifiedChain($expression);
        if (null !== $qualified) {
            return $qualified;
        }

        $operand = $this->run($expression->operand);
        $field = $expression->field->name;

        // Viral optional: if the operand is itself an optional, propagate `none`,
        // otherwise select optionally on the wrapped value.
        if ($operand instanceof OptionalValue) {
            $inner = $operand->value;
            if (null === $inner) {
                return OptionalValue::none();
            }

            return $this->optionalSelect($inner, $field, $expression->getSpan());
        }

        if ($expression->isOptional()) {
            return $this->optionalSelect($operand, $field, $expression->getSpan());
        }

        return $this->selectField($operand, $field, $expression->getSpan());
    }

    /**
     * Selects a field from a concrete message or map value.
     *
     * @throws EvaluationException If the field is absent or the value is not selectable.
     */
    private function selectField(Value $operand, string $field, Span $span): Value
    {
        if ($operand instanceof MessageValue) {
            $value = $operand->getField($field);
            if (null === $value) {
                throw new NoSuchKeyException(
                    sprintf('Field `%s` does not exist on message of type `%s`', $field, $operand->message::class),
                    $span,
                );
            }

            return $value;
        }

        if ($operand instanceof MapValue) {
            $value = $operand->get(MapKeyUtil::stringKey($field));
            if (null === $value) {
                throw new NoSuchKeyException(sprintf('Key `%s` does not exist in map', $field), $span);
            }

            return $value;
        }

        throw new NoSuchOverloadException(
            sprintf('Cannot access member `%s` on type `%s`', $field, $operand->getType()),
            $span,
        );
    }

    /**
     * Resolves a chain of plain identifier selectors (e.g. `x.y`, `.y.z`,
     * `google.protobuf.Timestamp`) using CEL's longest-prefix rule, or null when
     * it is not such a chain or should be handled as ordinary field selection.
     *
     * A leading-dot (absolute) chain resolves against the root environment,
     * ignoring comprehension-local bindings. A relative chain is only resolved
     * here when its root is not a bound variable; otherwise a bound root (a
     * message, or a comprehension variable) is left to field selection.
     *
     * @throws EvaluationException If an absolute reference cannot be resolved.
     */
    private function resolveQualifiedChain(MemberAccessExpression $expression): null|Value
    {
        // Every link must be a plain (non-optional) field access so the whole
        // expression spells a qualified identifier.
        $fields = [];
        $current = $expression;
        while ($current instanceof MemberAccessExpression) {
            if (null !== $current->question) {
                return null;
            }

            $fields[] = $current->field->name;
            $current = $current->operand;
        }

        if (!$current instanceof IdentifierExpression) {
            return null;
        }

        // Root identifier first, then the field selectors in source order.
        $segments = [$current->identifier->name, ...array_reverse($fields)];

        if (null !== $current->leadingDot) {
            // Absolute reference: resolve against the root namespace.
            $resolved = $this->resolveQualifiedSegments($segments, $this->rootEnvironment, $expression->getSpan());
            if (null !== $resolved) {
                return $resolved;
            }

            throw new NoSuchVariableException(
                sprintf('Variable `%s` is not defined in the environment', implode('.', $segments)),
                $expression->getSpan(),
            );
        }

        // A bound root variable (a message, or a comprehension variable) is
        // resolved through ordinary field selection, not as a qualified name.
        if (null !== $this->environment->getVariable($segments[0])) {
            return null;
        }

        return $this->resolveQualifiedSegments($segments, $this->environment, $expression->getSpan());
    }

    /**
     * Applies CEL's longest-prefix name resolution to a list of identifier
     * segments: the longest prefix that names a bound variable or a type is the
     * base, and any remaining segments are applied as field selections. Returns
     * null when no prefix resolves.
     *
     * @param non-empty-list<string> $segments
     *
     * @throws EvaluationException If a remaining segment cannot be selected.
     */
    private function resolveQualifiedSegments(
        array $segments,
        EnvironmentInterface $environment,
        Span $span,
    ): null|Value {
        for ($length = count($segments); $length >= 1; --$length) {
            $name = implode('.', array_slice($segments, 0, $length));
            $base = $environment->getVariable($name) ?? TypeValue::denotation($name);
            if (null === $base) {
                continue;
            }

            foreach (array_slice($segments, $length) as $field) {
                $base = $this->selectField($base, $field, $span);
            }

            return $base;
        }

        return null;
    }

    /**
     * Performs an optional field selection on a concrete value, returning an optional
     * that holds the field value when present and `optional.none()` when it is absent.
     *
     * @throws EvaluationException If the value does not support field selection.
     */
    private function optionalSelect(Value $base, string $field, Span $span): OptionalValue
    {
        if ($base instanceof MessageValue) {
            $value = $base->getField($field);

            return null === $value ? OptionalValue::none() : OptionalValue::of($value);
        }

        if ($base instanceof MapValue) {
            $value = $base->get(MapKeyUtil::stringKey($field));

            return null === $value ? OptionalValue::none() : OptionalValue::of($value);
        }

        throw new NoSuchOverloadException(
            sprintf('Cannot access member `%s` on type `%s`', $field, $base->getType()),
            $span,
        );
    }

    /**
     * @throws EvaluationException
     */
    private function index(IndexExpression $expression): Value
    {
        $operand = $this->run($expression->operand);

        // Viral optional: if the operand is itself an optional, propagate `none`,
        // otherwise index optionally into the wrapped value.
        if ($operand instanceof OptionalValue) {
            $inner = $operand->value;
            if (null === $inner) {
                return OptionalValue::none();
            }

            return $this->optionalIndex($inner, $this->run($expression->index), $expression);
        }

        if ($expression->isOptional()) {
            return $this->optionalIndex($operand, $this->run($expression->index), $expression);
        }

        if (!$operand instanceof ListValue && !$operand instanceof MapValue && !$operand instanceof MessageValue) {
            throw new NoSuchOverloadException(
                sprintf('Indexing is only supported on lists, maps, and messages, got `%s`', $operand->getType()),
                $expression->getSpan(),
            );
        }

        $index = $this->run($expression->index);

        if ($operand instanceof MessageValue) {
            if (!$index instanceof StringValue) {
                throw new NoSuchOverloadException(
                    sprintf('Message fields must be accessed by string, got `%s`', $index->getType()),
                    $expression->index->getSpan(),
                );
            }

            $field = $operand->getField($index->value);

            if (null === $field) {
                throw new NoSuchKeyException(
                    sprintf(
                        'Field `%s` does not exist on message of type `%s`',
                        $index->value,
                        $operand->message::class,
                    ),
                    $expression->getSpan(),
                );
            }

            return $field;
        }

        if ($operand instanceof MapValue) {
            $field = $this->mapGet($operand, $index, $expression->index->getSpan());
            if (null === $field) {
                throw new NoSuchKeyException(
                    sprintf('Key `%s` does not exist in map', $this->mapKeyLabel($index)),
                    $expression->getSpan(),
                );
            }

            return $field;
        }

        $position = MapKeyUtil::resolveIndex($index);
        if (null === $position) {
            throw new NoSuchOverloadException(
                sprintf('List indices must be an integer or integral double, got `%s`', $index->getType()),
                $expression->index->getSpan(),
            );
        }

        $value = $operand->value[$position] ?? null;
        if (null === $value) {
            throw new NoSuchKeyException(
                sprintf('Index `%d` is out of bounds for list of length `%d`', $position, count($operand->value)),
                $expression->getSpan(),
            );
        }

        return $value;
    }

    /**
     * Performs an optional index access on a concrete value, returning an optional that
     * holds the indexed value when present and `optional.none()` when the index is absent.
     *
     * @throws EvaluationException If the value does not support indexing or the index type is invalid.
     */
    private function optionalIndex(Value $base, Value $index, IndexExpression $expression): OptionalValue
    {
        if ($base instanceof ListValue) {
            $position = MapKeyUtil::resolveIndex($index);
            if (null === $position) {
                throw new NoSuchOverloadException(
                    sprintf('List indices must be an integer or integral double, got `%s`', $index->getType()),
                    $expression->index->getSpan(),
                );
            }

            $value = $base->value[$position] ?? null;
            if (null === $value) {
                return OptionalValue::none();
            }

            return OptionalValue::of($value);
        }

        if ($base instanceof MapValue) {
            $value = $this->mapGet($base, $index, $expression->index->getSpan());

            return null === $value ? OptionalValue::none() : OptionalValue::of($value);
        }

        if ($base instanceof MessageValue) {
            if (!$index instanceof StringValue) {
                throw new NoSuchOverloadException(
                    sprintf('Message fields must be accessed by string, got `%s`', $index->getType()),
                    $expression->index->getSpan(),
                );
            }

            $value = $base->getField($index->value);

            return null === $value ? OptionalValue::none() : OptionalValue::of($value);
        }

        throw new NoSuchOverloadException(
            sprintf('Indexing is only supported on lists, maps, and messages, got `%s`', $base->getType()),
            $expression->getSpan(),
        );
    }

    /**
     * Looks up a value in a map by a string or numeric key, supporting heterogeneous
     * numeric keys (an integer-valued numeric index matches an integer key). Returns
     * null when the key is absent.
     *
     * @throws NoSuchOverloadException If the index type cannot be used as a map key.
     */
    private function mapGet(MapValue $map, Value $index, Span $indexSpan): null|Value
    {
        if (!MapKeyUtil::isKeyType($index)) {
            throw new NoSuchOverloadException(
                sprintf(
                    'Map keys must be bool, string, integer, unsigned integer, or double, got `%s`',
                    $index->getType(),
                ),
                $indexSpan,
            );
        }

        $key = MapKeyUtil::resolve($index);

        return null === $key ? null : $map->get($key);
    }

    /**
     * Produces a human-readable label for a map index, used in "no such key" errors.
     */
    private function mapKeyLabel(Value $index): string
    {
        if ($index instanceof BooleanValue) {
            return $index->value ? 'true' : 'false';
        }

        if ($index instanceof StringValue) {
            return $index->value;
        }

        if ($index instanceof IntegerValue || $index instanceof UnsignedIntegerValue) {
            return (string) $index->value;
        }

        if ($index instanceof FloatValue) {
            $integer = MapKeyUtil::resolveIndex($index);

            return null === $integer ? $index->getType() : (string) $integer;
        }

        return $index->getType();
    }

    /**
     * @throws EvaluationException
     */
    private function identifier(IdentifierExpression $expression): Value
    {
        $name = $expression->identifier->name;

        // An absolute (leading-dot) reference resolves against the root
        // namespace, bypassing any comprehension-local binding of the name.
        $environment = null !== $expression->leadingDot ? $this->rootEnvironment : $this->environment;

        $value = $environment->getVariable($name);
        if (null !== $value) {
            return $value;
        }

        $type = TypeValue::denotation($name);
        if (null !== $type) {
            return $type;
        }

        throw new NoSuchVariableException(
            sprintf('Variable `%s` is not defined in the environment', $name),
            $expression->getSpan(),
        );
    }

    /**
     * @throws EvaluationException
     *
     * @mago-expect analysis:possibly-static-access-on-interface
     */
    private function message(MessageExpression $expression): Value
    {
        $classname = $expression->selector->name;
        $typename = $expression->selector->name;
        foreach ($expression->followingSelectors as $selector) {
            $classname .= '\\' . $selector->name;
            $typename .= '.' . $selector->name;
        }

        // Well-known protobuf types map onto native CEL values and are always
        // constructible, independent of the message configuration.
        if (str_starts_with($typename, 'google.protobuf.')) {
            $wellKnown = $this->constructWellKnownType($typename, $expression);
            if (null !== $wellKnown) {
                return $wellKnown;
            }
        }

        if ([] === $this->configuration->allowedMessageClasses) {
            throw new NoSuchTypeException(
                sprintf('Message type `%s` does not exist or is not allowed per configuration.', $typename),
                $expression->getSpan(),
            );
        }

        $foundClassname = null;
        $usingAlias = false;
        foreach ($this->configuration->messageClassAliases as $typeAlias => $targetClassname) {
            if (strcasecmp($typename, $typeAlias) !== 0) {
                continue;
            }

            $foundClassname = $targetClassname;
            break;
        }

        if (null === $foundClassname) {
            foreach ($this->configuration->allowedMessageClasses as $allowedClassname) {
                if (strcasecmp($classname, $allowedClassname) !== 0) {
                    continue;
                }

                $foundClassname = $allowedClassname;
                break;
            }

            if (
                null !== $foundClassname
                && $this->configuration->enforceMessageClassAliases
                && array_key_exists($foundClassname, $this->configuration->messageClassesToAliases)
            ) {
                // Pretend the class does not exist if using an alias is enforced
                throw new NoSuchTypeException(
                    sprintf('Message type `%s` does not exist or is not allowed per configuration.', $typename),
                    $expression->getSpan(),
                );
            }
        }

        if (null === $foundClassname) {
            throw new NoSuchTypeException(
                sprintf('Message type `%s` does not exist or is not allowed per configuration.', $typename),
                $expression->getSpan(),
            );
        }

        $fields = [];
        foreach ($expression->initializers as $initializer) {
            $value = $this->run($initializer->value);

            if ($initializer->isOptional()) {
                if (!$value instanceof OptionalValue) {
                    throw new InvalidOptionalConstructionException(
                        sprintf('Optional field initializer requires an optional value, got `%s`', $value->getType()),
                        $initializer->value->getSpan(),
                    );
                }

                if (null !== $value->value) {
                    $fields[$initializer->field->name] = $value->value;
                }

                continue;
            }

            $fields[$initializer->field->name] = $value;
        }

        try {
            return new MessageValue($foundClassname::fromCelFields($fields), $fields);
        } catch (Throwable $e) {
            throw new MessageConstructionException(
                sprintf('Failed to create message of type `%s`: %s', $typename, $e->getMessage()),
                $expression->getSpan(),
            );
        }
    }

    /**
     * Constructs a `google.protobuf` well-known type from a message literal, or
     * returns null when the type is not one cel-php represents natively (so the
     * caller falls back to the ordinary message path).
     *
     * @throws EvaluationException If an initializer names a field the type does not define.
     */
    private function constructWellKnownType(string $typename, MessageExpression $expression): null|Value
    {
        $allowedFields = WellKnownType::allowedFields($typename);
        if (null === $allowedFields) {
            return null;
        }

        $fields = [];
        foreach ($expression->initializers as $initializer) {
            $name = $initializer->field->name;
            if (!in_array($name, $allowedFields, true)) {
                throw new MessageConstructionException(
                    sprintf('Field `%s` is not defined on message type `%s`.', $name, $typename),
                    $initializer->field->getSpan(),
                );
            }

            $fields[$name] = $this->run($initializer->value);
        }

        return WellKnownType::construct($typename, $fields);
    }

    /**
     * @throws EvaluationException
     */
    private function call(CallExpression $expression): Value
    {
        // Try macros first
        $macro_result = $this->macroRegistry->tryExecute($expression, $this);
        if (null !== $macro_result) {
            return $macro_result;
        }

        // Namespaced global function calls, such as `optional.of(x)`, where the target
        // identifier (`optional`) is a reserved namespace rather than a variable.
        $namespaced_result = $this->namespacedCall($expression);
        if (null !== $namespaced_result) {
            return $namespaced_result;
        }

        // Fall back to regular function calls
        $arguments = [];
        if (null !== $expression->target) {
            $arguments[] = $this->run($expression->target);
        }

        foreach ($expression->arguments->elements as $arg) {
            $arguments[] = $this->run($arg);
        }

        $function = $this->registry->getFunction($expression, $arguments);
        if (null === $function) {
            // Maybe the function exists with a different signature?
            $available_signatures = $this->registry->getFunctionSignatures($expression);
            if (null === $available_signatures) {
                throw new NoSuchFunctionException(
                    sprintf('Function `%s` is not defined', $expression->function->name),
                    $expression->getSpan(),
                );
            }

            $argument_kinds = array_map(static fn(Value $arg): ValueKind => $arg->getKind(), $arguments);

            throw NoSuchOverloadException::forCall($expression, $available_signatures, $argument_kinds);
        }

        [$idempotent, $callable] = $function;
        if (!$idempotent) {
            $this->idempotent = false;
        }

        return $callable($expression, $arguments);
    }

    /**
     * Attempts to resolve a call whose target is a namespace identifier (e.g. `optional`)
     * to a registered dotted global function (e.g. `optional.of`).
     *
     * Returns null when the call is not a namespaced global function call, in which case
     * it is handled as an ordinary function or method call.
     *
     * @throws EvaluationException
     */
    private function namespacedCall(CallExpression $expression): null|Value
    {
        $target = $expression->target;
        if (!$target instanceof IdentifierExpression) {
            return null;
        }

        $namespace = $target->identifier->name;
        if ($this->environment->hasVariable($namespace)) {
            // A variable of the same name shadows the namespace.
            return null;
        }

        $name = $namespace . '.' . $expression->function->name;
        $signatures = $this->registry->getFunctionSignaturesByName($name);
        if (null === $signatures) {
            return null;
        }

        $arguments = [];
        foreach ($expression->arguments->elements as $arg) {
            $arguments[] = $this->run($arg);
        }

        $function = $this->registry->getFunctionByName($name, $arguments);
        if (null === $function) {
            $argument_kinds = array_map(static fn(Value $arg): ValueKind => $arg->getKind(), $arguments);

            throw NoSuchOverloadException::forCall($expression, $signatures, $argument_kinds);
        }

        [$idempotent, $callable] = $function;
        if (!$idempotent) {
            $this->idempotent = false;
        }

        return $callable($expression, $arguments);
    }
}
