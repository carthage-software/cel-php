<?php

declare(strict_types=1);

namespace Cel\Extension\Core\BinaryOperator\Handler\AdditionOperator;

use Cel\Exception\InternalException;
use Cel\Operator\BinaryOperatorOverloadHandlerInterface;
use Cel\Span\Span;
use Cel\Util\OperandUtil;
use Cel\Value\StringValue;
use Cel\Value\Value;
use Override;

final readonly class StringStringHandler implements BinaryOperatorOverloadHandlerInterface
{
    /**
     * @param Span $span The span of the binary expression.
     * @param Value $left The evaluated left operand.
     * @param Value $right The evaluated right operand.
     *
     * @return Value The result of the binary operation.
     *
     * @throws InternalException If operand type assertion fails.
     */
    #[Override]
    public function __invoke(Span $span, Value $left, Value $right): Value
    {
        $left = OperandUtil::assertLeft($left, StringValue::class);
        $right = OperandUtil::assertRight($right, StringValue::class);

        return new StringValue($left->value . $right->value);
    }
}
