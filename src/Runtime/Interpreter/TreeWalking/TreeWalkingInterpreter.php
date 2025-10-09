<?php

declare(strict_types=1);

namespace Cel\Runtime\Interpreter\TreeWalking;

use Cel\Runtime\Configuration;
use Cel\Runtime\Environment\EnvironmentInterface;
use Cel\Runtime\Exception\EvaluationException;
use Cel\Runtime\Exception\InvalidConditionTypeException;
use Cel\Runtime\Exception\InvalidMacroCallException;
use Cel\Runtime\Exception\MessageConstructionException;
use Cel\Runtime\Exception\NoSuchFunctionException;
use Cel\Runtime\Exception\NoSuchKeyException;
use Cel\Runtime\Exception\NoSuchOverloadException;
use Cel\Runtime\Exception\NoSuchTypeException;
use Cel\Runtime\Exception\NoSuchVariableException;
use Cel\Runtime\Exception\UnexpectedMapKeyTypeException;
use Cel\Runtime\Exception\UnsupportedOperationException;
use Cel\Runtime\Interpreter\InterpreterInterface;
use Cel\Runtime\OperationRegistry;
use Cel\Runtime\Value\BooleanValue;
use Cel\Runtime\Value\BytesValue;
use Cel\Runtime\Value\FloatValue;
use Cel\Runtime\Value\IntegerValue;
use Cel\Runtime\Value\ListValue;
use Cel\Runtime\Value\MapValue;
use Cel\Runtime\Value\MessageValue;
use Cel\Runtime\Value\NullValue;
use Cel\Runtime\Value\StringValue;
use Cel\Runtime\Value\UnsignedIntegerValue;
use Cel\Runtime\Value\Value;
use Cel\Runtime\Value\ValueKind;
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
use Override;
use Psl\Iter;
use Psl\Str;
use Psl\Str\Byte;
use Psl\Vec;
use Throwable;

/**
 * A tree-walking interpreter that evaluates expressions by recursively
 * traversing the expression tree.
 *
 * @mago-expect lint:kan-defect
 * @mago-expect lint:cyclomatic-complexity
 */
final class TreeWalkingInterpreter implements InterpreterInterface
{
    private bool $idempotent = true;

    public function __construct(
        private readonly Configuration $configuration,
        private readonly OperationRegistry $registry,
        private EnvironmentInterface $environment,
    ) {}

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
    public function run(Expression $expression): Value
    {
        if ($expression instanceof ParenthesizedExpression) {
            return $this->run($expression->expression);
        }

        if ($expression instanceof LiteralExpression) {
            return $this->literal($expression);
        }

        if ($expression instanceof ListExpression) {
            return $this->list($expression);
        }

        if ($expression instanceof MapExpression) {
            return $this->map($expression);
        }

        if ($expression instanceof UnaryExpression) {
            return $this->unary($expression);
        }

        if ($expression instanceof BinaryExpression) {
            return $this->binary($expression);
        }

        if ($expression instanceof ConditionalExpression) {
            return $this->conditional($expression);
        }

        if ($expression instanceof MemberAccessExpression) {
            return $this->memberAccess($expression);
        }

        if ($expression instanceof IndexExpression) {
            return $this->index($expression);
        }

        if ($expression instanceof IdentifierExpression) {
            return $this->identifier($expression);
        }

        if ($expression instanceof CallExpression) {
            return $this->call($expression);
        }

        if ($expression instanceof MessageExpression) {
            return $this->message($expression);
        }

        throw new UnsupportedOperationException(
            Str\format('Unsupported expression of type `%s`', $expression::class),
            $expression->getSpan(),
        );
    }

    /**
     * @throws EvaluationException
     */
    private function list(ListExpression $expression): Value
    {
        $values = [];
        foreach ($expression->elements as $element) {
            $values[] = $this->run($element);
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
            if (!$key instanceof StringValue && !$key instanceof IntegerValue) {
                throw new UnexpectedMapKeyTypeException(
                    Str\format('Map keys must be string, or integer, got `%s`', $key->getType()),
                    $entry->key->getSpan(),
                );
            }

            $values[$key->value] = $this->run($entry->value);
        }

        return new MapValue($values);
    }

    /**
     * @throws EvaluationException
     */
    private function literal(LiteralExpression $expression): Value
    {
        return match ($expression::class) {
            BoolLiteralExpression::class => new BooleanValue($expression->value),
            BytesLiteralExpression::class => new BytesValue($expression->value),
            FloatLiteralExpression::class => new FloatValue($expression->value),
            IntegerLiteralExpression::class => new IntegerValue($expression->value),
            NullLiteralExpression::class => new NullValue(),
            StringLiteralExpression::class => new StringValue($expression->value),
            UnsignedIntegerLiteralExpression::class => new UnsignedIntegerValue($expression->value),
            default => throw new UnsupportedOperationException(
                Str\format('Unsupported literal of type `%s`', $expression::class),
                $expression->getSpan(),
            ),
        };
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
                Str\format(
                    'No such overload for %s`%s`',
                    $expression->operator->kind->getSymbol(),
                    $operand->getType(),
                ),
                $expression->getSpan(),
            );
        }

        return $handler($operand, $expression->operand);
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
                return $handler($left, $right, $expression->left, $expression->right);
            }

            // Fallback error for AND
            throw new NoSuchOverloadException(
                Str\format(
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
                return $handler($left, $right, $expression->left, $expression->right);
            }

            // Fallback error for OR
            throw new NoSuchOverloadException(
                Str\format(
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
                Str\format(
                    'No such overload for `%s` %s `%s`',
                    $left->getType(),
                    $operator->getSymbol(),
                    $right->getType(),
                ),
                $expression->left->getSpan()->join($expression->right->getSpan()),
            );
        }

        return $handler($left, $right, $expression->left, $expression->right);
    }

    /**
     * @throws EvaluationException
     */
    private function conditional(ConditionalExpression $expression): Value
    {
        $condition = $this->run($expression->condition);
        if (!$condition instanceof BooleanValue) {
            throw new InvalidConditionTypeException(
                Str\format('Condition must be boolean, got `%s`', $condition->getType()),
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
        $operand = $this->run($expression->operand);
        if ($operand instanceof MessageValue) {
            $field = $operand->getField($expression->field->name);
            if (null === $field) {
                throw new NoSuchKeyException(
                    Str\format(
                        'Field `%s` does not exist on message of type `%s`',
                        $expression->field->name,
                        $operand->message::class,
                    ),
                    $expression->getSpan(),
                );
            }

            return $field;
        }

        if ($operand instanceof MapValue) {
            $field = $operand->get($expression->field->name);
            if (null === $field) {
                throw new NoSuchKeyException(
                    Str\format('Key `%s` does not exist in map', $expression->field->name),
                    $expression->getSpan(),
                );
            }

            return $field;
        }

        throw new NoSuchOverloadException(
            Str\format('Cannot access member `%s` on type `%s`', $expression->field->name, $operand->getType()),
            $expression->getSpan(),
        );
    }

    /**
     * @throws EvaluationException
     */
    private function index(IndexExpression $expression): Value
    {
        $operand = $this->run($expression->operand);
        if (!$operand instanceof ListValue && !$operand instanceof MapValue && !$operand instanceof MessageValue) {
            throw new NoSuchOverloadException(
                Str\format('Indexing is only supported on lists, maps, and messages, got `%s`', $operand->getType()),
                $expression->getSpan(),
            );
        }

        $index = $this->run($expression->index);

        if ($operand instanceof MessageValue) {
            if (!$index instanceof StringValue) {
                throw new NoSuchOverloadException(
                    Str\format('Message fields must be accessed by string, got `%s`', $index->getType()),
                    $expression->index->getSpan(),
                );
            }

            $field = $operand->getField($index->value);

            if (null === $field) {
                throw new NoSuchKeyException(
                    Str\format(
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
            if (!$index instanceof StringValue && !$index instanceof IntegerValue) {
                throw new NoSuchOverloadException(
                    Str\format('Map keys must be string or integer, got `%s`', $index->getType()),
                    $expression->index->getSpan(),
                );
            }

            $field = $operand->get($index->value);
            if (null === $field) {
                throw new NoSuchKeyException(
                    Str\format('Key `%s` does not exist in map', $index->value),
                    $expression->getSpan(),
                );
            }

            return $field;
        }

        if (!$index instanceof IntegerValue) {
            throw new NoSuchOverloadException(
                Str\format('List indices must be integer, got `%s`', $index->getType()),
                $expression->index->getSpan(),
            );
        }

        if ($index->value < 0 || $index->value >= Iter\count($operand->value)) {
            throw new NoSuchKeyException(
                Str\format(
                    'Index `%d` is out of bounds for list of length `%d`',
                    $index->value,
                    Iter\count($operand->value),
                ),
                $expression->getSpan(),
            );
        }

        return $operand->value[$index->value];
    }

    /**
     * @throws EvaluationException
     */
    private function identifier(IdentifierExpression $expression): Value
    {
        $value = $this->environment->getVariable($expression->identifier->name);
        if (null === $value) {
            throw new NoSuchVariableException(
                Str\format('Variable `%s` is not defined in the environment', $expression->identifier->name),
                $expression->getSpan(),
            );
        }

        return $value;
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

        if ([] === $this->configuration->allowedMessageClasses) {
            throw new NoSuchTypeException(
                Str\format('Message type `%s` does not exist or is not allowed per configuration.', $typename),
                $expression->getSpan(),
            );
        }

        $foundClassname = null;
        $usingAlias = false;
        foreach ($this->configuration->messageClassAliases as $typeAlias => $targetClassname) {
            if (Byte\compare_ci($typename, $typeAlias) === 0) {
                $foundClassname = $targetClassname;

                break;
            }
        }

        if (null === $foundClassname) {
            foreach ($this->configuration->allowedMessageClasses as $allowedClassname) {
                if (Byte\compare_ci($classname, $allowedClassname) === 0) {
                    $foundClassname = $allowedClassname;
                    break;
                }
            }

            if (
                null !== $foundClassname
                && $this->configuration->enforceMessageClassAliases
                && Iter\contains_key($this->configuration->messageClassesToAliases, $foundClassname)
            ) {
                // Pretend the class does not exist if using an alias is enforced
                throw new NoSuchTypeException(
                    Str\format('Message type `%s` does not exist or is not allowed per configuration.', $typename),
                    $expression->getSpan(),
                );
            }
        }

        if (null === $foundClassname) {
            throw new NoSuchTypeException(
                Str\format('Message type `%s` does not exist or is not allowed per configuration.', $typename),
                $expression->getSpan(),
            );
        }

        $fields = [];
        foreach ($expression->initializers as $initializer) {
            $fields[$initializer->field->name] = $this->run($initializer->value);
        }

        try {
            return new MessageValue($foundClassname::fromCelFields($fields), $fields);
        } catch (Throwable $e) {
            throw new MessageConstructionException(
                Str\format('Failed to create message of type `%s`: %s', $typename, $e->getMessage()),
                $expression->getSpan(),
            );
        }
    }

    /**
     * @throws EvaluationException
     */
    private function call(CallExpression $expression): Value
    {
        $macro_result = $this->macro($expression);
        if (null !== $macro_result) {
            return $macro_result;
        }

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
                    Str\format('Function `%s` is not defined', $expression->function->name),
                    $expression->getSpan(),
                );
            }

            $argument_kinds = Vec\map($arguments, static fn(Value $arg): ValueKind => $arg->getKind());

            throw NoSuchOverloadException::forCall($expression, $available_signatures, $argument_kinds);
        }

        [$idempotent, $callable] = $function;
        if (!$idempotent) {
            $this->idempotent = false;
        }

        return $callable($expression, $arguments);
    }

    /**
     * @throws EvaluationException
     */
    private function macro(CallExpression $expression): null|Value
    {
        if (!$this->configuration->enableMacros) {
            return null;
        }

        return match ($expression->function->name) {
            'has' => $this->hasMacro($expression),
            'all' => $this->allMacro($expression),
            'exists' => $this->existsMacro($expression),
            'exists_one' => $this->existsOneMacro($expression),
            'filter' => $this->filterMacro($expression),
            'map' => $this->mapMacro($expression),
            default => null,
        };
    }

    /**
     * @throws EvaluationException
     */
    private function hasMacro(CallExpression $expression): null|Value
    {
        if (null !== $expression->target) {
            return null;
        }

        $argument = $expression->arguments->elements[0] ?? null;
        if (null === $argument || $expression->arguments->count() > 1) {
            return null;
        }

        if (!$argument instanceof MemberAccessExpression) {
            throw new InvalidMacroCallException(
                'The `has` macro requires a single member access expression as an argument.',
                $argument->getSpan(),
            );
        }

        $operand = $this->run($argument->operand);
        if (!$operand instanceof MessageValue && !$operand instanceof MapValue) {
            throw new InvalidMacroCallException(
                Str\format('The `has` macro requires a message or map operand, got `%s`', $operand->getType()),
                $argument->operand->getSpan(),
            );
        }

        if ($operand instanceof MessageValue) {
            return new BooleanValue($operand->hasField($argument->field->name));
        }

        return new BooleanValue($operand->has($argument->field->name));
    }

    /**
     * @throws EvaluationException
     */
    private function allMacro(CallExpression $expression): null|Value
    {
        if (null === $expression->target) {
            return null;
        }

        $name = $expression->arguments->elements[0] ?? null;
        $callback = $expression->arguments->elements[1] ?? null;
        if (null === $name || null === $callback || $expression->arguments->count() > 2) {
            return null;
        }

        if (!$name instanceof IdentifierExpression) {
            throw new InvalidMacroCallException(
                'The `all` macro requires the first argument to be an identifier.',
                $name->getSpan(),
            );
        }

        $target = $this->run($expression->target);
        if (!$target instanceof ListValue && !$target instanceof MapValue) {
            throw new InvalidMacroCallException(
                Str\format('The `all` macro requires a list or map target, got `%s`', $target->getType()),
                $expression->target->getSpan(),
            );
        }

        $items = $target instanceof ListValue ? $target->value : Vec\map(Vec\keys($target->value), Value::from(...));

        $environment = $this->environment->fork();
        try {
            $all_true = true;
            foreach ($items as $value) {
                $this->environment->addVariable($name->identifier->name, $value);

                $result = $this->run($callback);
                if (!$result instanceof BooleanValue) {
                    throw new InvalidMacroCallException(
                        Str\format('The `all` macro predicate must result in a boolean, got `%s`', $result->getType()),
                        $callback->getSpan(),
                    );
                }

                if (!$result->value) {
                    $all_true = false;
                    break;
                }
            }
        } finally {
            $this->environment = $environment;
        }

        return new BooleanValue($all_true);
    }

    /**
     * @throws EvaluationException
     */
    private function existsMacro(CallExpression $expression): null|Value
    {
        if (null === $expression->target) {
            return null;
        }

        $name = $expression->arguments->elements[0] ?? null;
        $callback = $expression->arguments->elements[1] ?? null;
        if (null === $name || null === $callback || $expression->arguments->count() > 2) {
            return null;
        }

        if (!$name instanceof IdentifierExpression) {
            throw new InvalidMacroCallException(
                'The `exists` macro requires the first argument to be an identifier.',
                $name->getSpan(),
            );
        }

        $target = $this->run($expression->target);
        if (!$target instanceof ListValue && !$target instanceof MapValue) {
            throw new InvalidMacroCallException(
                Str\format('The `exists` macro requires a list or map target, got `%s`', $target->getType()),
                $expression->target->getSpan(),
            );
        }

        $items = $target instanceof ListValue ? $target->value : Vec\map(Vec\keys($target->value), Value::from(...));

        $environment = $this->environment->fork();
        try {
            $found_one = false;
            foreach ($items as $value) {
                $this->environment->addVariable($name->identifier->name, $value);

                $result = $this->run($callback);
                if (!$result instanceof BooleanValue) {
                    throw new InvalidMacroCallException(
                        Str\format(
                            'The `exists` macro predicate must result in a boolean, got `%s`',
                            $result->getType(),
                        ),
                        $callback->getSpan(),
                    );
                }

                if ($result->value) {
                    $found_one = true;
                    break;
                }
            }
        } finally {
            $this->environment = $environment;
        }

        return new BooleanValue($found_one);
    }

    /**
     * @throws EvaluationException
     */
    private function existsOneMacro(CallExpression $expression): null|Value
    {
        if (null === $expression->target) {
            return null;
        }

        $name = $expression->arguments->elements[0] ?? null;
        $callback = $expression->arguments->elements[1] ?? null;
        if (null === $name || null === $callback || $expression->arguments->count() > 2) {
            return null;
        }

        if (!$name instanceof IdentifierExpression) {
            throw new InvalidMacroCallException(
                'The `exists_one` macro requires the first argument to be an identifier.',
                $name->getSpan(),
            );
        }

        $target = $this->run($expression->target);
        if (!$target instanceof ListValue && !$target instanceof MapValue) {
            throw new InvalidMacroCallException(
                Str\format('The `exists_one` macro requires a list or map target, got `%s`', $target->getType()),
                $expression->target->getSpan(),
            );
        }

        $items = $target instanceof ListValue ? $target->value : Vec\map(Vec\keys($target->value), Value::from(...));

        $environment = $this->environment->fork();
        $true_count = 0;
        try {
            foreach ($items as $value) {
                $this->environment->addVariable($name->identifier->name, $value);

                $result = $this->run($callback);
                if (!$result instanceof BooleanValue) {
                    throw new InvalidMacroCallException(
                        Str\format(
                            'The `exists_one` macro predicate must result in a boolean, got `%s`',
                            $result->getType(),
                        ),
                        $callback->getSpan(),
                    );
                }

                if ($result->value) {
                    $true_count++;
                }
            }
        } finally {
            $this->environment = $environment;
        }

        return new BooleanValue(1 === $true_count);
    }

    /**
     * @throws EvaluationException
     */
    private function mapMacro(CallExpression $expression): null|Value
    {
        if (null === $expression->target) {
            return null;
        }

        $argCount = $expression->arguments->count();
        if ($argCount < 2 || $argCount > 3) {
            return null;
        }

        $name = $expression->arguments->elements[0];
        if (!$name instanceof IdentifierExpression) {
            throw new InvalidMacroCallException(
                'The `map` macro requires the first argument to be an identifier.',
                $name->getSpan(),
            );
        }

        $target = $this->run($expression->target);
        if (!$target instanceof ListValue && !$target instanceof MapValue) {
            throw new InvalidMacroCallException(
                Str\format('The `map` macro requires a list or map target, got `%s`', $target->getType()),
                $expression->target->getSpan(),
            );
        }

        $variableName = $name->identifier->name;
        $environment = $this->environment->fork();
        $results = [];

        try {
            $filterCallback = 3 === $argCount ? $expression->arguments->elements[1] : null;
            $transformCallback = 3 === $argCount
                ? $expression->arguments->elements[2]
                : $expression->arguments->elements[1];

            $items = $target instanceof ListValue
                ? $target->value
                : Vec\map(Vec\keys($target->value), Value::from(...));

            foreach ($items as $item) {
                $this->environment->addVariable($variableName, $item);

                if (null !== $filterCallback) {
                    $filterResult = $this->run($filterCallback);
                    if (!$filterResult instanceof BooleanValue) {
                        throw new InvalidMacroCallException(
                            Str\format(
                                'The `map` macro filter must result in a boolean, got `%s`',
                                $filterResult->getType(),
                            ),
                            $filterCallback->getSpan(),
                        );
                    }
                    if (!$filterResult->value) {
                        continue;
                    }
                }

                $results[] = $this->run($transformCallback);
            }
        } finally {
            $this->environment = $environment;
        }

        return new ListValue($results);
    }

    /**
     * @throws EvaluationException
     */
    private function filterMacro(CallExpression $expression): null|Value
    {
        if (null === $expression->target) {
            return null;
        }

        $name = $expression->arguments->elements[0] ?? null;
        $callback = $expression->arguments->elements[1] ?? null;
        if (null === $name || null === $callback || $expression->arguments->count() > 2) {
            return null;
        }

        if (!$name instanceof IdentifierExpression) {
            throw new InvalidMacroCallException(
                'The `filter` macro requires the first argument to be an identifier.',
                $name->getSpan(),
            );
        }

        $target = $this->run($expression->target);
        if (!$target instanceof ListValue && !$target instanceof MapValue) {
            throw new InvalidMacroCallException(
                Str\format('The `filter` macro requires a list or map target, got `%s`', $target->getType()),
                $expression->target->getSpan(),
            );
        }

        $variableName = $name->identifier->name;
        $environment = $this->environment->fork();
        $results = [];

        try {
            $items = $target instanceof ListValue
                ? $target->value
                : Vec\map(Vec\keys($target->value), Value::from(...));

            foreach ($items as $item) {
                $this->environment->addVariable($variableName, $item);

                $filterResult = $this->run($callback);
                if (!$filterResult instanceof BooleanValue) {
                    throw new InvalidMacroCallException(
                        Str\format(
                            'The `filter` macro predicate must result in a boolean, got `%s`',
                            $filterResult->getType(),
                        ),
                        $callback->getSpan(),
                    );
                }

                if ($filterResult->value) {
                    $results[] = $item;
                }
            }
        } finally {
            $this->environment = $environment;
        }

        return new ListValue($results);
    }
}
