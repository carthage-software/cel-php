<?php

declare(strict_types=1);

namespace Cel\Extension\Core\BinaryOperator\Handler\InOperator;

use Cel\Exception\InternalException;
use Cel\Operator\BinaryOperatorOverloadHandlerInterface;
use Cel\Syntax\Binary\BinaryExpression;
use Cel\Util\OperandUtil;
use Cel\Value\BooleanValue;
use Cel\Value\ListValue;
use Cel\Value\UnsignedIntegerValue;
use Cel\Value\Value;
use Override;
use Psl\Iter;

final readonly class UnsignedIntegerListHandler implements BinaryOperatorOverloadHandlerInterface
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
        $left = OperandUtil::assertLeft($left, UnsignedIntegerValue::class);
        $right = OperandUtil::assertRight($right, ListValue::class);

        return new BooleanValue(Iter\any($right->value, static fn(Value $item): bool => $item->isEqual($left)));
    }
}
