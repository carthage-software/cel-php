<?php

declare(strict_types=1);

namespace Cel\Extension\Decimal\UnaryOperator\Handler\NegationOperator;

use Cel\Exception\InternalException;
use Cel\Extension\Decimal\DecimalNumber;
use Cel\Operator\UnaryOperatorOverloadHandlerInterface;
use Cel\Syntax\Unary\UnaryExpression;
use Cel\Util\OperandUtil;
use Cel\Value\MessageValue;
use Cel\Value\Value;
use Override;
use Psl\Str;
use Throwable;

use function assert;

final readonly class DecimalNumberHandler implements UnaryOperatorOverloadHandlerInterface
{
    /**
     * @param UnaryExpression $expression The unary expression being evaluated.
     * @param Value $operand The evaluated operand.
     *
     * @return Value The result of the unary operation.
     *
     * @throws InternalException If the Decimal operation fails or for OperandUtil calls.
     */
    #[Override]
    public function __invoke(UnaryExpression $expression, Value $operand): Value
    {
        $operand = OperandUtil::assert($operand, MessageValue::class);
        assert($operand->message instanceof DecimalNumber, 'Operand must be DecimalNumber');

        try {
            $result = $operand->message->getInner()->negate();
        } catch (Throwable $e) {
            throw InternalException::forMessage(Str\format('Decimal negation failed: %s', $e->getMessage()), $e);
        }

        return new DecimalNumber($result)->toCelValue();
    }
}
