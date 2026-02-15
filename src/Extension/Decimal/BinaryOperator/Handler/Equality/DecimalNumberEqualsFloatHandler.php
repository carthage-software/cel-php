<?php

declare(strict_types=1);

namespace Cel\Extension\Decimal\BinaryOperator\Handler\Equality;

use Cel\Exception\InternalException;
use Cel\Extension\Decimal\DecimalNumber;
use Cel\Operator\BinaryOperatorOverloadHandlerInterface;
use Cel\Span\Span;
use Cel\Syntax\Binary\BinaryOperatorKind;
use Cel\Util\OperandUtil;
use Cel\Value\BooleanValue;
use Cel\Value\FloatValue;
use Cel\Value\MessageValue;
use Cel\Value\Value;
use Decimal\Decimal;
use Override;
use Psl\Str;
use Throwable;

use function assert;

/**
 * Handles equality comparison of a DecimalNumber and a Float.
 */
final readonly class DecimalNumberEqualsFloatHandler implements BinaryOperatorOverloadHandlerInterface
{
    public function __construct(
        private BinaryOperatorKind $operator,
    ) {}

    /**
     * @param Span $span The span of the binary expression.
     * @param Value $left The evaluated left operand (must be MessageValue containing DecimalNumber).
     * @param Value $right The evaluated right operand (must be FloatValue).
     *
     * @return Value The result of comparing equality of the DecimalNumber and Float.
     *
     * @throws InternalException If the Decimal operation fails or for OperandUtil calls.
     */
    #[Override]
    public function __invoke(Span $span, Value $left, Value $right): Value
    {
        $left = OperandUtil::assertLeft($left, MessageValue::class);
        $right = OperandUtil::assertRight($right, FloatValue::class);

        assert($left->message instanceof DecimalNumber, 'Left operand must be DecimalNumber');

        try {
            $equals = $left->message->getInner()->equals(new Decimal((string) $right->value));
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
