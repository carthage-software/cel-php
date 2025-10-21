<?php

declare(strict_types=1);

namespace Cel\Extension\Decimal\BinaryOperator\Handler\Equality;

use Cel\Exception\InternalException;
use Cel\Extension\Decimal\DecimalNumber;
use Cel\Operator\BinaryOperatorOverloadHandlerInterface;
use Cel\Syntax\Binary\BinaryExpression;
use Cel\Syntax\Binary\BinaryOperatorKind;
use Cel\Util\OperandUtil;
use Cel\Value\BooleanValue;
use Cel\Value\MessageValue;
use Cel\Value\UnsignedIntegerValue;
use Cel\Value\Value;
use Decimal\Decimal;
use Override;
use Psl\Str;
use Throwable;

use function assert;

/**
 * Handles equality comparison of an UnsignedInteger and a DecimalNumber.
 */
final readonly class UnsignedIntegerEqualsDecimalNumberHandler implements BinaryOperatorOverloadHandlerInterface
{
    public function __construct(
        private BinaryOperatorKind $operator,
    ) {}

    /**
     * @param BinaryExpression $expression The binary expression being evaluated.
     * @param Value $left The evaluated left operand (must be UnsignedIntegerValue).
     * @param Value $right The evaluated right operand (must be MessageValue containing DecimalNumber).
     *
     * @return Value The result of comparing equality of the UnsignedInteger and DecimalNumber.
     *
     * @throws InternalException If the Decimal operation fails or for OperandUtil calls.
     */
    #[Override]
    public function __invoke(BinaryExpression $expression, Value $left, Value $right): Value
    {
        $left = OperandUtil::assertLeft($left, UnsignedIntegerValue::class);
        $right = OperandUtil::assertRight($right, MessageValue::class);

        assert($right->message instanceof DecimalNumber, 'Right operand must be DecimalNumber');

        try {
            $equals = new Decimal((string) $left->value)->equals($right->message->getInner());
        } catch (Throwable $e) {
            throw InternalException::forMessage(
                Str\format('Decimal equality comparison failed: %s', $e->getMessage()),
                $e,
            );
        }
        $result = $this->operator === BinaryOperatorKind::Equal ? $equals : !$equals;

        return new BooleanValue($result);
    }
}
