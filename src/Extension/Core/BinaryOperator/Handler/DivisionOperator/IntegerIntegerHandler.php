<?php

declare(strict_types=1);

namespace Cel\Extension\Core\BinaryOperator\Handler\DivisionOperator;

use Cel\Exception\DivisionByZeroException;
use Cel\Exception\InternalException;
use Cel\Operator\BinaryOperatorOverloadHandlerInterface;
use Cel\Syntax\Binary\BinaryExpression;
use Cel\Util\OperandUtil;
use Cel\Value\IntegerValue;
use Cel\Value\Value;
use Override;

use function intdiv;

use const PHP_INT_MIN;

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
     * @throws DivisionByZeroException If division by zero is attempted.
     */
    #[Override]
    public function __invoke(BinaryExpression $expression, Value $left, Value $right): Value
    {
        $left = OperandUtil::assertLeft($left, IntegerValue::class);
        $right = OperandUtil::assertRight($right, IntegerValue::class);

        if (0 === $right->value || -1 === $right->value && PHP_INT_MIN === $left->value) {
            throw new DivisionByZeroException(
                'Failed to evaluate division: division by zero',
                $expression->left->getSpan()->join($expression->right->getSpan()),
            );
        }

        // @mago-expect analysis:unhandled-thrown-type(2) - checked above.
        return new IntegerValue(intdiv($left->value, $right->value));
    }
}
