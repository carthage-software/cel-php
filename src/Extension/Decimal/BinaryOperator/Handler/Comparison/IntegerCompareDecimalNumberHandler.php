<?php

declare(strict_types=1);

namespace Cel\Extension\Decimal\BinaryOperator\Handler\Comparison;

use Cel\Exception\InternalException;
use Cel\Extension\Decimal\DecimalNumber;
use Cel\Operator\BinaryOperatorOverloadHandlerInterface;
use Cel\Syntax\Binary\BinaryExpression;
use Cel\Syntax\Binary\BinaryOperatorKind;
use Cel\Util\OperandUtil;
use Cel\Value\BooleanValue;
use Cel\Value\IntegerValue;
use Cel\Value\MessageValue;
use Cel\Value\Value;
use Decimal\Decimal;
use LogicException;
use Override;
use Psl\Str;
use Throwable;

use function assert;

/**
 * Handles comparison of an Integer and a DecimalNumber.
 */
final readonly class IntegerCompareDecimalNumberHandler implements BinaryOperatorOverloadHandlerInterface
{
    public function __construct(
        private BinaryOperatorKind $operator,
    ) {}

    /**
     * @param BinaryExpression $expression The binary expression being evaluated.
     * @param Value $left The evaluated left operand (must be IntegerValue).
     * @param Value $right The evaluated right operand (must be MessageValue containing DecimalNumber).
     *
     * @return Value The result of comparing the Integer and DecimalNumber.
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
            $comparison = new Decimal((string) $left->value)->compareTo($right->message->getInner());
        } catch (Throwable $e) {
            throw InternalException::forMessage(Str\format('Decimal comparison failed: %s', $e->getMessage()), $e);
        }
        $result = $this->compareResult($comparison);

        return new BooleanValue($result);
    }

    private function compareResult(int $comparison): bool
    {
        return match ($this->operator) {
            BinaryOperatorKind::LessThan => $comparison < 0,
            BinaryOperatorKind::LessThanOrEqual => $comparison <= 0,
            BinaryOperatorKind::GreaterThan => $comparison > 0,
            BinaryOperatorKind::GreaterThanOrEqual => $comparison >= 0,
            default => throw new LogicException('Invalid comparison operator'),
        };
    }
}
