<?php

declare(strict_types=1);

namespace Cel\Extension\Core\BinaryOperator\Handler\DivisionOperator;

use Cel\Exception\DivisionByZeroException;
use Cel\Exception\InternalException;
use Cel\Operator\BinaryOperatorOverloadHandlerInterface;
use Cel\Span\Span;
use Cel\Util\OperandUtil;
use Cel\Value\IntegerValue;
use Cel\Value\Value;
use Override;
use Psl\Math;

final readonly class IntegerIntegerHandler implements BinaryOperatorOverloadHandlerInterface
{
    /**
     * @param Span $span The span of the binary expression.
     * @param Value $left The evaluated left operand.
     * @param Value $right The evaluated right operand.
     *
     * @return Value The result of the binary operation.
     *
     * @throws InternalException If operand type assertion fails.
     * @throws DivisionByZeroException If division by zero is attempted.
     */
    #[Override]
    public function __invoke(Span $span, Value $left, Value $right): Value
    {
        $left = OperandUtil::assertLeft($left, IntegerValue::class);
        $right = OperandUtil::assertRight($right, IntegerValue::class);

        try {
            return new IntegerValue(Math\div($left->value, $right->value));
        } catch (Math\Exception\DivisionByZeroException|Math\Exception\ArithmeticException $exception) {
            throw new DivisionByZeroException('Failed to evaluate division: division by zero', $span, $exception);
        }
    }
}
