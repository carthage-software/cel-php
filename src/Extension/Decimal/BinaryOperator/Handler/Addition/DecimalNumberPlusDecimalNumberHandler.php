<?php

declare(strict_types=1);

namespace Cel\Extension\Decimal\BinaryOperator\Handler\Addition;

use Cel\Exception\InternalException;
use Cel\Extension\Decimal\DecimalNumber;
use Cel\Operator\BinaryOperatorOverloadHandlerInterface;
use Cel\Syntax\Binary\BinaryExpression;
use Cel\Util\OperandUtil;
use Cel\Value\MessageValue;
use Cel\Value\Value;
use Override;
use Psl\Str;
use Throwable;

use function assert;

/**
 * Handles addition of two DecimalNumber values.
 */
final readonly class DecimalNumberPlusDecimalNumberHandler implements BinaryOperatorOverloadHandlerInterface
{
    /**
     * @param BinaryExpression $expression The binary expression being evaluated.
     * @param Value $left The evaluated left operand (must be MessageValue containing DecimalNumber).
     * @param Value $right The evaluated right operand (must be MessageValue containing DecimalNumber).
     *
     * @return Value The result of adding the two DecimalNumber values.
     *
     * @throws InternalException If the Decimal operation fails or for OperandUtil calls.
     */
    #[Override]
    public function __invoke(BinaryExpression $expression, Value $left, Value $right): Value
    {
        $left = OperandUtil::assertLeft($left, MessageValue::class);
        $right = OperandUtil::assertRight($right, MessageValue::class);

        assert($left->message instanceof DecimalNumber, 'Left operand must be DecimalNumber');
        assert($right->message instanceof DecimalNumber, 'Right operand must be DecimalNumber');

        try {
            $result = $left->message->getInner()->add($right->message->getInner());
        } catch (Throwable $e) {
            throw InternalException::forMessage(Str\format('Decimal addition failed: %s', $e->getMessage()), $e);
        }

        return new DecimalNumber($result)->toCelValue();
    }
}
