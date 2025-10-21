<?php

declare(strict_types=1);

namespace Cel\Extension\Core\BinaryOperator\Handler\LogicalAndOperator;

use Cel\Exception\InternalException;
use Cel\Operator\BinaryOperatorOverloadHandlerInterface;
use Cel\Syntax\Binary\BinaryExpression;
use Cel\Util\OperandUtil;
use Cel\Value\BooleanValue;
use Cel\Value\Value;
use Override;

final readonly class BooleanBooleanHandler implements BinaryOperatorOverloadHandlerInterface
{
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
        $left = OperandUtil::assertLeft($left, BooleanValue::class);
        $right = OperandUtil::assertRight($right, BooleanValue::class);

        return new BooleanValue($left->value && $right->value);
    }
}
