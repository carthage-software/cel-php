<?php

declare(strict_types=1);

namespace Cel\Extension\Decimal\BinaryOperator\Handler\Modulo;

use Cel\Exception\InternalException;
use Cel\Extension\Decimal\DecimalNumber;
use Cel\Operator\BinaryOperatorOverloadHandlerInterface;
use Cel\Syntax\Binary\BinaryExpression;
use Cel\Util\OperandUtil;
use Cel\Value\IntegerValue;
use Cel\Value\MessageValue;
use Cel\Value\Value;
use Decimal\Decimal;
use Override;
use Psl\Str;
use Throwable;

use function assert;

/**
 * Handles modulo operation of an Integer and a DecimalNumber.
 */
final readonly class IntegerModuloDecimalNumberHandler implements BinaryOperatorOverloadHandlerInterface
{
    /**
     * @param BinaryExpression $expression The binary expression being evaluated.
     * @param Value $left The evaluated left operand (must be IntegerValue).
     * @param Value $right The evaluated right operand (must be MessageValue containing DecimalNumber).
     *
     * @return Value The result of the modulo operation of the Integer and DecimalNumber.
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
            $result = new Decimal((string) $left->value)->mod($right->message->getInner());
        } catch (Throwable $e) {
            throw InternalException::forMessage(Str\format('Decimal modulo failed: %s', $e->getMessage()), $e);
        }

        return new DecimalNumber($result)->toCelValue();
    }
}
