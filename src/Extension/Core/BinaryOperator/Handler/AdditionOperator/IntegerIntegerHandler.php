<?php

declare(strict_types=1);

namespace Cel\Extension\Core\BinaryOperator\Handler\AdditionOperator;

use Cel\Exception\InternalException;
use Cel\Exception\OverflowException;
use Cel\Operator\BinaryOperatorOverloadHandlerInterface;
use Cel\Syntax\Binary\BinaryExpression;
use Cel\Util\IntegerMath;
use Cel\Util\OperandUtil;
use Cel\Value\IntegerValue;
use Cel\Value\Value;
use Override;

/**
 * @internal
 */
final readonly class IntegerIntegerHandler implements BinaryOperatorOverloadHandlerInterface
{
    /**
     * @param BinaryExpression $expression The binary expression being evaluated.
     * @param Value $left The evaluated left operand.
     * @param Value $right The evaluated right operand.
     *
     * @return Value The result of the binary operation.
     *
     * @throws InternalException If operand type assertion fails.
     * @throws OverflowException If the addition overflows the 64-bit integer range.
     */
    #[Override]
    public function __invoke(BinaryExpression $expression, Value $left, Value $right): Value
    {
        $left = OperandUtil::assertLeft($left, IntegerValue::class);
        $right = OperandUtil::assertRight($right, IntegerValue::class);

        $result = IntegerMath::add($left->value, $right->value);
        if (null === $result) {
            throw new OverflowException(
                'Integer overflow on addition',
                $expression->left->getSpan()->join($expression->right->getSpan()),
            );
        }

        return new IntegerValue($result);
    }
}
