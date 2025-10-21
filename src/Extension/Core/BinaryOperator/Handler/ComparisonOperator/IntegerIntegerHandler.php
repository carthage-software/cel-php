<?php

declare(strict_types=1);

namespace Cel\Extension\Core\BinaryOperator\Handler\ComparisonOperator;

use Cel\Exception\InternalException;
use Cel\Operator\BinaryOperatorOverloadHandlerInterface;
use Cel\Syntax\Binary\BinaryExpression;
use Cel\Util\OperandUtil;
use Cel\Value\BooleanValue;
use Cel\Value\IntegerValue;
use Cel\Value\Value;
use Override;

final readonly class IntegerIntegerHandler implements BinaryOperatorOverloadHandlerInterface
{
    /**
     * @param callable(Value, Value): bool $comparator
     */
    public function __construct(
        private mixed $comparator,
    ) {}

    /**
     * @param BinaryExpression $expression The binary expression being evaluated.
     * @param Value $left The evaluated left operand.
     * @param Value $right The evaluated right operand.
     *
     * @return Value The result of the binary operation.
     *
     * @throws InternalException If operand type assertion fails.
     */
    #[Override]
    public function __invoke(BinaryExpression $expression, Value $left, Value $right): Value
    {
        $left = OperandUtil::assertLeft($left, IntegerValue::class);
        $right = OperandUtil::assertRight($right, IntegerValue::class);

        return new BooleanValue(($this->comparator)($left, $right));
    }
}
