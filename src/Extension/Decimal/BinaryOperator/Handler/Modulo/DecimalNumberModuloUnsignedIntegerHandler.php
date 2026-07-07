<?php

declare(strict_types=1);

namespace Cel\Extension\Decimal\BinaryOperator\Handler\Modulo;

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
 * Handles modulo operation of a DecimalNumber and an UnsignedInteger.
 *
 * @internal
 */
final readonly class DecimalNumberModuloUnsignedIntegerHandler implements BinaryOperatorOverloadHandlerInterface
{
    /**
     * @param BinaryExpression $expression The binary expression being evaluated.
     * @param Value $left The evaluated left operand (must be MessageValue containing DecimalNumber).
     * @param Value $right The evaluated right operand (must be UnsignedIntegerValue).
     *
     * @return Value The result of the modulo operation of the DecimalNumber and UnsignedInteger.
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
            $result = $left->message->getInner()->mod(DecimalFactory::from((string) $right->value));
        } catch (Throwable $e) {
            throw InternalException::forMessage(sprintf('Decimal modulo failed: %s', $e->getMessage()), $e);
        }

        return new DecimalNumber($result)->toCelValue();
    }
}
