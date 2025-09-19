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
use Cel\Runtime\Exception\OverflowException;
use Cel\Runtime\Exception\UnexpectedMapKeyTypeException;
use Cel\Runtime\Exception\UnsupportedOperationException;
use Cel\Runtime\Function\FunctionRegistry;
use Cel\Runtime\Interpreter\InterpreterInterface;
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
use Cel\Syntax\Unary\UnaryOperatorKind;
use Closure;
use DivisionByZeroError;
use Override;
use Psl\Iter;
use Psl\Math;
use Psl\Str;
use Psl\Str\Byte;
use Psl\Vec;
use Throwable;

use function bcadd;
use function bccomp;
use function bcdiv;
use function bcmod;
use function bcmul;
use function bcsub;

/**
 * A tree-walking interpreter that evaluates expressions by recursively
 * traversing the expression tree.
 *
 * @mago-expect lint:too-many-methods
 * @mago-expect lint:kan-defect
 * @mago-expect lint:cyclomatic-complexity
 */
final class TreeWalkingInterpreter implements InterpreterInterface
{
    private bool $idempotent = true;

    public function __construct(
        private readonly Configuration $configuration,
        private readonly FunctionRegistry $registry,
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

        if (UnaryOperatorKind::Negate === $expression->operator->kind) {
            if ($operand instanceof IntegerValue) {
                return new IntegerValue(-$operand->value);
            }

            if ($operand instanceof FloatValue) {
                return new FloatValue(-$operand->value);
            }

            throw new NoSuchOverloadException(
                Str\format('Cannot negate value of type `%s`', $operand->getType()),
                $expression->getSpan(),
            );
        }

        if ($operand instanceof BooleanValue) {
            return new BooleanValue(!$operand->value);
        }

        throw new NoSuchOverloadException(
            Str\format(
                'Cannot apply operator `%s` to value of type `%s`',
                $expression->operator->kind->name,
                $operand->getType(),
            ),
            $expression->getSpan(),
        );
    }

    /**
     * @throws EvaluationException
     */
    private function binary(BinaryExpression $expression): Value
    {
        return match ($expression->operator->kind) {
            BinaryOperatorKind::LessThan => $this->binaryLessThan($expression->left, $expression->right),
            BinaryOperatorKind::LessThanOrEqual => $this->binaryLessThanOrEqual($expression->left, $expression->right),
            BinaryOperatorKind::GreaterThan => $this->binaryGreaterThan($expression->left, $expression->right),
            BinaryOperatorKind::GreaterThanOrEqual => $this->binaryGreaterThanOrEqual(
                $expression->left,
                $expression->right,
            ),
            BinaryOperatorKind::Equal => $this->binaryEquals($expression->left, $expression->right),
            BinaryOperatorKind::NotEqual => $this->binaryNotEquals($expression->left, $expression->right),
            BinaryOperatorKind::In => $this->binaryIn($expression->left, $expression->right),
            BinaryOperatorKind::Plus => $this->binaryPlus($expression->left, $expression->right),
            BinaryOperatorKind::Minus => $this->binaryMinus($expression->left, $expression->right),
            BinaryOperatorKind::Multiply => $this->binaryMultiply($expression->left, $expression->right),
            BinaryOperatorKind::Divide => $this->binaryDivide($expression->left, $expression->right),
            BinaryOperatorKind::Modulo => $this->binaryModulo($expression->left, $expression->right),
            BinaryOperatorKind::And => $this->binaryAnd($expression->left, $expression->right),
            BinaryOperatorKind::Or => $this->binaryOr($expression->left, $expression->right),
        };
    }

    /**
     * @throws EvaluationException
     */
    private function binaryLessThan(Expression $left, Expression $right): BooleanValue
    {
        return $this->compare($left, $right, '<', static fn(Value $a, Value $b): bool => $a->isLessThan($b));
    }

    /**
     * @throws EvaluationException
     */
    private function binaryLessThanOrEqual(Expression $left, Expression $right): BooleanValue
    {
        return $this->compare(
            $left,
            $right,
            '<=',
            static fn(Value $a, Value $b): bool => $a->isLessThan($b) || $a->isEqual($b),
        );
    }

    /**
     * @throws EvaluationException
     */
    private function binaryGreaterThan(Expression $left, Expression $right): BooleanValue
    {
        return $this->compare($left, $right, '>', static fn(Value $a, Value $b): bool => $a->isGreaterThan($b));
    }

    /**
     * @throws EvaluationException
     */
    private function binaryGreaterThanOrEqual(Expression $left, Expression $right): BooleanValue
    {
        return $this->compare(
            $left,
            $right,
            '>=',
            static fn(Value $a, Value $b): bool => $a->isGreaterThan($b) || $a->isEqual($b),
        );
    }

    /**
     * @throws EvaluationException
     */
    private function binaryEquals(Expression $left, Expression $right): BooleanValue
    {
        return $this->compare(
            $left,
            $right,
            '==',
            static fn(Value $a, Value $b): bool => $a->isEqual($b),
            supports_aggregates: true,
        );
    }

    /**
     * @throws EvaluationException
     */
    private function binaryNotEquals(Expression $left, Expression $right): BooleanValue
    {
        return $this->compare(
            $left,
            $right,
            '==',
            static fn(Value $a, Value $b): bool => !$a->isEqual($b),
            supports_aggregates: true,
        );
    }

    /**
     * @param (Closure(Value, Value): bool) $comparator
     *
     * @throws EvaluationException
     */
    private function compare(
        Expression $left,
        Expression $right,
        string $operator,
        Closure $comparator,
        bool $supports_aggregates = false,
    ): BooleanValue {
        $left_value = $this->run($left);
        if (!$supports_aggregates && $left_value->isAggregate()) {
            throw new UnsupportedOperationException(
                Str\format(
                    'Operator `%s` does not support aggregate types, got `%s`',
                    $operator,
                    $left_value->getType(),
                ),
                $left->getSpan(),
            );
        }

        $right_value = $this->run($right);

        try {
            return new BooleanValue($comparator($left_value, $right_value));
        } catch (UnsupportedOperationException $exception) {
            throw $exception->withSpan($left->getSpan()->join($right->getSpan()));
        }
    }

    /**
     * @throws EvaluationException
     */
    private function binaryIn(Expression $left, Expression $right): Value
    {
        $left_value = $this->run($left);
        $right_value = $this->run($right);

        if (!$right_value instanceof ListValue) {
            throw new NoSuchOverloadException(
                Str\format('Right operand of `in` must be a list, got `%s`', $right_value->getType()),
                $right->getSpan(),
            );
        }

        return new BooleanValue(Iter\any($right_value->value, static fn(Value $item): bool => $item->isEqual(
            $left_value,
        )));
    }

    /**
     * @throws EvaluationException
     */
    private function binaryPlus(Expression $left, Expression $right): Value
    {
        $left_value = $this->run($left);
        $right_value = $this->run($right);

        return match ($left_value::class) {
            IntegerValue::class => $right_value instanceof IntegerValue
                ? new IntegerValue($left_value->value + $right_value->value)
                : throw new NoSuchOverloadException(
                    Str\format('Cannot add `%s` and `%s`', $left_value->getType(), $right_value->getType()),
                    $left->getSpan()->join($right->getSpan()),
                ),
            UnsignedIntegerValue::class => $right_value instanceof UnsignedIntegerValue
                ? new UnsignedIntegerValue(bcadd((string) $left_value->value, (string) $right_value->value))
                : throw new NoSuchOverloadException(
                    Str\format('Cannot add `%s` and `%s`', $left_value->getType(), $right_value->getType()),
                    $left->getSpan()->join($right->getSpan()),
                ),
            FloatValue::class => $right_value instanceof FloatValue
                ? new FloatValue($left_value->value + $right_value->value)
                : throw new NoSuchOverloadException(
                    Str\format('Cannot add `%s` and `%s`', $left_value->getType(), $right_value->getType()),
                    $left->getSpan()->join($right->getSpan()),
                ),
            StringValue::class => $right_value instanceof StringValue
                ? new StringValue($left_value->value . $right_value->value)
                : throw new NoSuchOverloadException(
                    Str\format('Cannot concatenate `%s` and `%s`', $left_value->getType(), $right_value->getType()),
                    $left->getSpan()->join($right->getSpan()),
                ),
            BytesValue::class => $right_value instanceof BytesValue
                ? new BytesValue($left_value->value . $right_value->value)
                : throw new NoSuchOverloadException(
                    Str\format('Cannot concatenate `%s` and `%s`', $left_value->getType(), $right_value->getType()),
                    $left->getSpan()->join($right->getSpan()),
                ),
            ListValue::class => $right_value instanceof ListValue
                ? new ListValue([...$left_value->value, ...$right_value->value])
                : throw new NoSuchOverloadException(
                    Str\format('Cannot concatenate `%s` and `%s`', $left_value->getType(), $right_value->getType()),
                    $left->getSpan()->join($right->getSpan()),
                ),
            default => throw new NoSuchOverloadException(
                Str\format('Operator `+` is not supported for type `%s`', $left_value->getType()),
                $left->getSpan(),
            ),
        };
    }

    /**
     * @throws EvaluationException
     */
    private function binaryMinus(Expression $left, Expression $right): Value
    {
        $left_value = $this->run($left);
        $right_value = $this->run($right);

        return match ($left_value::class) {
            IntegerValue::class => $right_value instanceof IntegerValue
                ? new IntegerValue($left_value->value - $right_value->value)
                : throw new NoSuchOverloadException(
                    Str\format('Cannot subtract `%s` from `%s`', $right_value->getType(), $left_value->getType()),
                    $left->getSpan()->join($right->getSpan()),
                ),
            UnsignedIntegerValue::class => (static function () use (
                $left_value,
                $right_value,
                $left,
                $right,
            ): UnsignedIntegerValue {
                if (!$right_value instanceof UnsignedIntegerValue) {
                    throw new NoSuchOverloadException(
                        Str\format('Cannot subtract `%s` from `%s`', $right_value->getType(), $left_value->getType()),
                        $left->getSpan()->join($right->getSpan()),
                    );
                }

                $res = bcsub((string) $left_value->value, (string) $right_value->value);
                if (bccomp($res, '0') === -1) {
                    throw new OverflowException(
                        'Unsigned integer overflow on subtraction',
                        $left->getSpan()->join($right->getSpan()),
                    );
                }

                return new UnsignedIntegerValue($res);
            })(),
            FloatValue::class => $right_value instanceof FloatValue
                ? new FloatValue($left_value->value - $right_value->value)
                : throw new NoSuchOverloadException(
                    Str\format('Cannot subtract `%s` from `%s`', $right_value->getType(), $left_value->getType()),
                    $left->getSpan()->join($right->getSpan()),
                ),
            default => throw new NoSuchOverloadException(
                Str\format('Operator `-` is not supported for type `%s`', $left_value->getType()),
                $left->getSpan(),
            ),
        };
    }

    /**
     * @throws EvaluationException
     */
    private function binaryMultiply(Expression $left, Expression $right): Value
    {
        $left_value = $this->run($left);
        $right_value = $this->run($right);

        return match ($left_value::class) {
            IntegerValue::class => $right_value instanceof IntegerValue
                ? new IntegerValue($left_value->value * $right_value->value)
                : throw new NoSuchOverloadException(
                    Str\format('Cannot multiply `%s` by `%s`', $left_value->getType(), $right_value->getType()),
                    $left->getSpan()->join($right->getSpan()),
                ),
            UnsignedIntegerValue::class => $right_value instanceof UnsignedIntegerValue
                ? new UnsignedIntegerValue(bcmul((string) $left_value->value, (string) $right_value->value))
                : throw new NoSuchOverloadException(
                    Str\format('Cannot multiply `%s` by `%s`', $left_value->getType(), $right_value->getType()),
                    $left->getSpan()->join($right->getSpan()),
                ),
            FloatValue::class => $right_value instanceof FloatValue
                ? new FloatValue($left_value->value * $right_value->value)
                : throw new NoSuchOverloadException(
                    Str\format('Cannot multiply `%s` by `%s`', $left_value->getType(), $right_value->getType()),
                    $left->getSpan()->join($right->getSpan()),
                ),
            default => throw new NoSuchOverloadException(
                Str\format('Operator `*` is not supported for type `%s`', $left_value->getType()),
                $left->getSpan(),
            ),
        };
    }

    /**
     * @throws EvaluationException
     */
    private function binaryDivide(Expression $left, Expression $right): Value
    {
        $left_value = $this->run($left);
        $right_value = $this->run($right);

        try {
            return match ($left_value::class) {
                IntegerValue::class => $right_value instanceof IntegerValue
                    ? new IntegerValue(Math\div($left_value->value, $right_value->value))
                    : throw new NoSuchOverloadException(
                        Str\format('Cannot divide `%s` by `%s`', $left_value->getType(), $right_value->getType()),
                        $left->getSpan()->join($right->getSpan()),
                    ),
                UnsignedIntegerValue::class => $right_value instanceof UnsignedIntegerValue
                    ? new UnsignedIntegerValue(bcdiv((string) $left_value->value, (string) $right_value->value))
                    : throw new NoSuchOverloadException(
                        Str\format('Cannot divide `%s` by `%s`', $left_value->getType(), $right_value->getType()),
                        $left->getSpan()->join($right->getSpan()),
                    ),
                FloatValue::class => $right_value instanceof FloatValue
                    ? new FloatValue($left_value->value / $right_value->value)
                    : throw new NoSuchOverloadException(
                        Str\format('Cannot divide `%s` by `%s`', $left_value->getType(), $right_value->getType()),
                        $left->getSpan()->join($right->getSpan()),
                    ),
                default => throw new NoSuchOverloadException(
                    Str\format('Operator `/` is not supported for type `%s`', $left_value->getType()),
                    $left->getSpan(),
                ),
            };
        } catch (Math\Exception\DivisionByZeroException|DivisionByZeroError $exception) { // @mago-expect analysis:avoid-catching-error
            throw new EvaluationException(
                'Failed to evaluate division: division by zero',
                $left->getSpan()->join($right->getSpan()),
                $exception,
            );
        }
    }

    /**
     * @throws EvaluationException
     */
    private function binaryModulo(Expression $left, Expression $right): Value
    {
        $left_value = $this->run($left);
        $right_value = $this->run($right);

        try {
            return match ($left_value::class) {
                IntegerValue::class => $right_value instanceof IntegerValue
                    ? new IntegerValue($left_value->value % $right_value->value)
                    : throw new NoSuchOverloadException(
                        Str\format(
                            'Cannot apply modulo to `%s` and `%s`',
                            $left_value->getType(),
                            $right_value->getType(),
                        ),
                        $left->getSpan()->join($right->getSpan()),
                    ),
                UnsignedIntegerValue::class => $right_value instanceof UnsignedIntegerValue
                    ? new UnsignedIntegerValue(bcmod((string) $left_value->value, (string) $right_value->value))
                    : throw new NoSuchOverloadException(
                        Str\format(
                            'Cannot apply modulo to `%s` and `%s`',
                            $left_value->getType(),
                            $right_value->getType(),
                        ),
                        $left->getSpan()->join($right->getSpan()),
                    ),
                default => throw new NoSuchOverloadException(
                    Str\format('Operator `%%` is not supported for type `%s`', $left_value->getType()),
                    $left->getSpan(),
                ),
            };
        } catch (DivisionByZeroError $exception) { // @mago-expect analysis:avoid-catching-error
            throw new EvaluationException(
                'Failed to evaluate modulo: division by zero',
                $left->getSpan()->join($right->getSpan()),
                $exception,
            );
        }
    }

    /**
     * @throws EvaluationException
     */
    private function binaryAnd(Expression $left, Expression $right): Value
    {
        // Short-circuit evaluation for AND
        if ($left instanceof BoolLiteralExpression && $left->value) {
            return new BooleanValue(false);
        }

        if ($right instanceof BoolLiteralExpression && $right->value) {
            return new BooleanValue(false);
        }

        $left_value = $this->run($left);
        if (!$left_value instanceof BooleanValue) {
            throw new NoSuchOverloadException(
                Str\format('Left operand of AND must be boolean, got `%s`', $left_value->getType()),
                $left->getSpan(),
            );
        }

        if (!$left_value->value) {
            return new BooleanValue(false); // Short-circuit if left is false
        }

        $right_value = $this->run($right);
        if (!$right_value instanceof BooleanValue) {
            throw new NoSuchOverloadException(
                Str\format('Right operand of AND must be boolean, got `%s`', $right_value->getType()),
                $right->getSpan(),
            );
        }

        return new BooleanValue($right_value->value);
    }

    /**
     * @throws EvaluationException
     */
    private function binaryOr(Expression $left, Expression $right): Value
    {
        // Short-circuit evaluation for OR
        if ($left instanceof BoolLiteralExpression && !$left->value) {
            return new BooleanValue(true);
        }

        if ($right instanceof BoolLiteralExpression && !$right->value) {
            return new BooleanValue(true);
        }

        $left_value = $this->run($left);
        if (!$left_value instanceof BooleanValue) {
            throw new NoSuchOverloadException(
                Str\format('Left operand of OR must be boolean, got `%s`', $left_value->getType()),
                $left->getSpan(),
            );
        }

        if ($left_value->value) {
            return new BooleanValue(true); // Short-circuit if left is true
        }

        $right_value = $this->run($right);
        if (!$right_value instanceof BooleanValue) {
            throw new NoSuchOverloadException(
                Str\format('Right operand of OR must be boolean, got `%s`', $right_value->getType()),
                $right->getSpan(),
            );
        }

        return new BooleanValue($right_value->value);
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

        if ($foundClassname === null) {
            foreach ($this->configuration->allowedMessageClasses as $allowedClassname) {
                if (Byte\compare_ci($classname, $allowedClassname) === 0) {
                    $foundClassname = $allowedClassname;
                    break;
                }
            }

            if (
                $foundClassname !== null
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
        if ($expression->target !== null) {
            $arguments[] = $this->run($expression->target);
        }

        foreach ($expression->arguments->elements as $arg) {
            $arguments[] = $this->run($arg);
        }

        $function = $this->registry->get($expression, $arguments);
        if (null === $function) {
            // Maybe the function exists with a different signature?
            $available_signatures = $this->registry->getSignatures($expression);
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

        /** @var list<Value> $items */
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

        /** @var list<Value> $items */
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

        /** @var list<Value> $items */
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

        return new BooleanValue($true_count === 1);
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

        /** @var Expression $name */
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
            $filterCallback = $argCount === 3 ? $expression->arguments->elements[1] : null;
            $transformCallback = $argCount === 3
                ? $expression->arguments->elements[2]
                : $expression->arguments->elements[1];

            /** @var list<Value> $items */
            $items = $target instanceof ListValue
                ? $target->value
                : Vec\map(Vec\keys($target->value), Value::from(...));

            foreach ($items as $item) {
                $this->environment->addVariable($variableName, $item);

                if ($filterCallback !== null) {
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
            /** @var list<Value> $items */
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
