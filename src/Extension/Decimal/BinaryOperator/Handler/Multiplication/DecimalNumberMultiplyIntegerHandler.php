<?php

declare(strict_types=1);

namespace Cel\Extension\Decimal\BinaryOperator\Handler\Multiplication;

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
 * Handles multiplication of a DecimalNumber and an Integer.
 */
final readonly class DecimalNumberMultiplyIntegerHandler implements BinaryOperatorOverloadHandlerInterface
{
    /**
     * @param BinaryExpression $expression The binary expression being evaluated.
     * @param Value $left The evaluated left operand (must be MessageValue containing DecimalNumber).
     * @param Value $right The evaluated right operand (must be IntegerValue).
     *
     * @return Value The result of multiplying the DecimalNumber and Integer.
     *
     * @throws InternalException If the Decimal operation fails or for OperandUtil calls.
     */
    #[Override]
    public function __invoke(BinaryExpression $expression, Value $left, Value $right): Value
    {
        $left = OperandUtil::assertLeft($left, MessageValue::class);
        $right = OperandUtil::assertRight($right, IntegerValue::class);

        assert($left->message instanceof DecimalNumber, 'Left operand must be DecimalNumber');

        try {
            $result = $left->message->getInner()->mul(new Decimal((string) $right->value));
        } catch (Throwable $e) {
            throw InternalException::forMessage(Str\format('Decimal multiplication failed: %s', $e->getMessage()), $e);
        }

        return new DecimalNumber($result)->toCelValue();
    }
}
