<?php

declare(strict_types=1);

namespace Cel\Extension\Decimal\BinaryOperator\Handler\Multiplication;

use Cel\Exception\InternalException;
use Cel\Extension\Decimal\DecimalNumber;
use Cel\Operator\BinaryOperatorOverloadHandlerInterface;
use Cel\Span\Span;
use Cel\Util\OperandUtil;
use Cel\Value\MessageValue;
use Cel\Value\Value;
use Override;
use Psl\Str;
use Throwable;

use function assert;

/**
 * Handles multiplication of two DecimalNumber values.
 */
final readonly class DecimalNumberMultiplyDecimalNumberHandler implements BinaryOperatorOverloadHandlerInterface
{
    /**
     * @param Span $span The span of the binary expression.
     * @param Value $left The evaluated left operand (must be MessageValue containing DecimalNumber).
     * @param Value $right The evaluated right operand (must be MessageValue containing DecimalNumber).
     *
     * @return Value The result of multiplying the two DecimalNumber values.
     *
     * @throws InternalException If the Decimal operation fails or for OperandUtil calls.
     */
    #[Override]
    public function __invoke(Span $span, Value $left, Value $right): Value
    {
        $left = OperandUtil::assertLeft($left, MessageValue::class);
        $right = OperandUtil::assertRight($right, MessageValue::class);

        assert($left->message instanceof DecimalNumber, 'Left operand must be DecimalNumber');
        assert($right->message instanceof DecimalNumber, 'Right operand must be DecimalNumber');

        try {
            $result = $left->message->getInner()->mul($right->message->getInner());
        } catch (Throwable $e) {
            throw InternalException::forMessage(Str\format('Decimal multiplication failed: %s', $e->getMessage()), $e);
        }

        return new DecimalNumber($result)->toCelValue();
    }
}
