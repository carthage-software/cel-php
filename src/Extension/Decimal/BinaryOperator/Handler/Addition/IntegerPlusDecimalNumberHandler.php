<?php

declare(strict_types=1);

namespace Cel\Extension\Decimal\BinaryOperator\Handler\Addition;

use Cel\Exception\InternalException;
use Cel\Extension\Decimal\Message\DecimalNumber;
use Cel\Extension\Decimal\Util\DecimalFactory;
use Cel\Operator\BinaryOperatorOverloadHandlerInterface;
use Cel\Syntax\Binary\BinaryExpression;
use Cel\Util\OperandUtil;
use Cel\Value\IntegerValue;
use Cel\Value\MessageValue;
use Cel\Value\Value;
use Override;
use Throwable;

use function assert;
use function sprintf;

/**
 * Handles addition of an Integer and a DecimalNumber.
 *
 * @internal
 */
final readonly class IntegerPlusDecimalNumberHandler implements BinaryOperatorOverloadHandlerInterface
{
    /**
     * @param BinaryExpression $expression The binary expression being evaluated.
     * @param Value $left The evaluated left operand (must be IntegerValue).
     * @param Value $right The evaluated right operand (must be MessageValue containing DecimalNumber).
     *
     * @return Value The result of adding the Integer and DecimalNumber.
     *
     * @throws InternalException If the Decimal operation fails or for OperandUtil calls.
     */
    #[Override]
    public function __invoke(BinaryExpression $expression, Value $left, Value $right): Value
    {
        $left = OperandUtil::assertLeft($left, IntegerValue::class);
        $right = OperandUtil::assertRight($right, MessageValue::class);

        assert($right->message instanceof DecimalNumber, 'Right operand must be DecimalNumber');

        try {
            $result = DecimalFactory::from((string) $left->value)->add($right->message->getInner());
        } catch (Throwable $e) {
            throw InternalException::forMessage(sprintf('Decimal addition failed: %s', $e->getMessage()), $e);
        }

        return new DecimalNumber($result)->toCelValue();
    }
}
