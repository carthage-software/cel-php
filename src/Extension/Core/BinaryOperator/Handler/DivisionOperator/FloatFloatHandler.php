<?php

declare(strict_types=1);

namespace Cel\Extension\Core\BinaryOperator\Handler\DivisionOperator;

use Cel\Exception\EvaluationException;
use Cel\Exception\InternalException;
use Cel\Operator\BinaryOperatorOverloadHandlerInterface;
use Cel\Span\Span;
use Cel\Util\OperandUtil;
use Cel\Value\FloatValue;
use Cel\Value\Value;
use DivisionByZeroError;
use Override;

final readonly class FloatFloatHandler implements BinaryOperatorOverloadHandlerInterface
{
    /**
     * @param Span $span The span of the binary expression.
     * @param Value $left The evaluated left operand.
     * @param Value $right The evaluated right operand.
     *
     * @return Value The result of the binary operation.
     *
     * @throws InternalException If operand type assertion fails.
     * @throws EvaluationException If division by zero is attempted.
     */
    #[Override]
    public function __invoke(Span $span, Value $left, Value $right): Value
    {
        $left = OperandUtil::assertLeft($left, FloatValue::class);
        $right = OperandUtil::assertRight($right, FloatValue::class);

        try {
            return new FloatValue($left->value / $right->value);
        } catch (DivisionByZeroError $exception) { // @mago-expect analysis:avoid-catching-error
            throw new EvaluationException('Failed to evaluate division: division by zero', $span, $exception);
        }
    }
}
