<?php

declare(strict_types=1);

namespace Cel\Extension\Decimal\BinaryOperator\Handler\Multiplication;

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
 * Handles multiplication of a DecimalNumber and an Integer.
 *
 * @internal
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
            $result = $left->message->getInner()->mul(DecimalFactory::from((string) $right->value));
        } catch (Throwable $e) {
            throw InternalException::forMessage(sprintf('Decimal multiplication failed: %s', $e->getMessage()), $e);
        }

        return new DecimalNumber($result)->toCelValue();
    }
}
