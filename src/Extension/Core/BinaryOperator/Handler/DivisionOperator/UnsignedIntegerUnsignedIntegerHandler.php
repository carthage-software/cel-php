<?php

declare(strict_types=1);

namespace Cel\Extension\Core\BinaryOperator\Handler\DivisionOperator;

use Cel\Exception\EvaluationException;
use Cel\Exception\InternalException;
use Cel\Operator\BinaryOperatorOverloadHandlerInterface;
use Cel\Syntax\Binary\BinaryExpression;
use Cel\Util\OperandUtil;
use Cel\Value\UnsignedIntegerValue;
use Cel\Value\Value;
use DivisionByZeroError;
use Override;

use function bcdiv;

final readonly class UnsignedIntegerUnsignedIntegerHandler implements BinaryOperatorOverloadHandlerInterface
{
    /**
     * @param BinaryExpression $expression The binary expression being evaluated.
     * @param Value $left The evaluated left operand.
     * @param Value $right The evaluated right operand.
     *
     * @return Value The result of the binary operation.
     *
     * @throws InternalException If operand type assertion fails.
     * @throws EvaluationException If division by zero is attempted.
     */
    #[Override]
    public function __invoke(BinaryExpression $expression, Value $left, Value $right): Value
    {
        $left = OperandUtil::assertLeft($left, UnsignedIntegerValue::class);
        $right = OperandUtil::assertRight($right, UnsignedIntegerValue::class);

        try {
            return new UnsignedIntegerValue(bcdiv((string) $left->value, (string) $right->value));
        } catch (DivisionByZeroError $exception) { // @mago-expect analysis:avoid-catching-error
            throw new EvaluationException(
                'Failed to evaluate division: division by zero',
                $expression->left->getSpan()->join($expression->right->getSpan()),
                $exception,
            );
        }
    }
}
