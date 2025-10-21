<?php

declare(strict_types=1);

namespace Cel\Extension\Decimal\BinaryOperator\Handler\Division;

use Cel\Exception\InternalException;
use Cel\Extension\Decimal\DecimalNumber;
use Cel\Operator\BinaryOperatorOverloadHandlerInterface;
use Cel\Syntax\Binary\BinaryExpression;
use Cel\Util\OperandUtil;
use Cel\Value\MessageValue;
use Cel\Value\UnsignedIntegerValue;
use Cel\Value\Value;
use Decimal\Decimal;
use Override;
use Psl\Str;
use Throwable;

use function assert;

/**
 * Handles division of a DecimalNumber by an UnsignedInteger.
 */
final readonly class DecimalNumberDivideUnsignedIntegerHandler implements BinaryOperatorOverloadHandlerInterface
{
    /**
     * @param BinaryExpression $expression The binary expression being evaluated.
     * @param Value $left The evaluated left operand (must be MessageValue containing DecimalNumber).
     * @param Value $right The evaluated right operand (must be UnsignedIntegerValue).
     *
     * @return Value The result of dividing the DecimalNumber by the UnsignedInteger.
     *
     * @throws InternalException If the Decimal operation fails or for OperandUtil calls.
     */
    #[Override]
    public function __invoke(BinaryExpression $expression, Value $left, Value $right): Value
    {
        $left = OperandUtil::assertLeft($left, MessageValue::class);
        $right = OperandUtil::assertRight($right, UnsignedIntegerValue::class);

        assert($left->message instanceof DecimalNumber, 'Left operand must be DecimalNumber');

        try {
            $result = $left->message->getInner()->div(new Decimal((string) $right->value));
        } catch (Throwable $e) {
            throw InternalException::forMessage(Str\format('Decimal division failed: %s', $e->getMessage()), $e);
        }

        return new DecimalNumber($result)->toCelValue();
    }
}
