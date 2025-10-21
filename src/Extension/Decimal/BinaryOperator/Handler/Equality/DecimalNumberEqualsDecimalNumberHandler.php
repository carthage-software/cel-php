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
use Cel\Value\Value;
use Override;
use Psl\Str;
use Throwable;

use function assert;

/**
 * Handles equality comparison of two DecimalNumber values.
 */
final readonly class DecimalNumberEqualsDecimalNumberHandler implements BinaryOperatorOverloadHandlerInterface
{
    public function __construct(
        private BinaryOperatorKind $operator,
    ) {}

    /**
     * @param BinaryExpression $expression The binary expression being evaluated.
     * @param Value $left The evaluated left operand (must be MessageValue containing DecimalNumber).
     * @param Value $right The evaluated right operand (must be MessageValue containing DecimalNumber).
     *
     * @return Value The result of comparing equality of the two DecimalNumber values.
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
            $equals = $left->message->getInner()->equals($right->message->getInner());
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
