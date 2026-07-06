<?php

declare(strict_types=1);

namespace Cel\Extension\Core\UnaryOperator\Handler\NegationOperator;

use Cel\Exception\InternalException;
use Cel\Exception\OverflowException;
use Cel\Operator\UnaryOperatorOverloadHandlerInterface;
use Cel\Syntax\Unary\UnaryExpression;
use Cel\Util\IntegerMath;
use Cel\Util\OperandUtil;
use Cel\Value\IntegerValue;
use Cel\Value\Value;
use Override;

final readonly class IntegerHandler implements UnaryOperatorOverloadHandlerInterface
{
    /**
     * @param UnaryExpression $expression The unary expression being evaluated.
     * @param Value $operand The evaluated operand.
     *
     * @return Value The result of the unary operation.
     *
     * @throws InternalException If operand type assertion fails.
     * @throws OverflowException If negating the operand overflows the 64-bit integer range.
     */
    #[Override]
    public function __invoke(UnaryExpression $expression, Value $operand): Value
    {
        $operand = OperandUtil::assert($operand, IntegerValue::class);

        $result = IntegerMath::negate($operand->value);
        if (null === $result) {
            throw new OverflowException('Integer overflow on negation', $expression->getSpan());
        }

        return new IntegerValue($result);
    }
}
