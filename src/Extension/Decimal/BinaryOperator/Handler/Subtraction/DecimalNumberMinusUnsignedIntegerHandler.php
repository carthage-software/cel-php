<?php

declare(strict_types=1);

namespace Cel\Extension\Decimal\BinaryOperator\Handler\Subtraction;

use Cel\Exception\InternalException;
use Cel\Extension\Decimal\Message\DecimalNumber;
use Cel\Extension\Decimal\Util\DecimalFactory;
use Cel\Operator\BinaryOperatorOverloadHandlerInterface;
use Cel\Syntax\Binary\BinaryExpression;
use Cel\Util\OperandUtil;
use Cel\Value\MessageValue;
use Cel\Value\UnsignedIntegerValue;
use Cel\Value\Value;
use Override;
use Throwable;

use function assert;
use function sprintf;

/**
 * Handles subtraction of an UnsignedInteger from a DecimalNumber.
 *
 * @internal
 */
final readonly class DecimalNumberMinusUnsignedIntegerHandler implements BinaryOperatorOverloadHandlerInterface
{
    /**
     * @param BinaryExpression $expression The binary expression being evaluated.
     * @param Value $left The evaluated left operand (must be MessageValue containing DecimalNumber).
     * @param Value $right The evaluated right operand (must be UnsignedIntegerValue).
     *
     * @return Value The result of subtracting the UnsignedInteger from the DecimalNumber.
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
            $result = $left->message->getInner()->sub(DecimalFactory::from((string) $right->value));
        } catch (Throwable $e) {
            throw InternalException::forMessage(sprintf('Decimal subtraction failed: %s', $e->getMessage()), $e);
        }

        return new DecimalNumber($result)->toCelValue();
    }
}
