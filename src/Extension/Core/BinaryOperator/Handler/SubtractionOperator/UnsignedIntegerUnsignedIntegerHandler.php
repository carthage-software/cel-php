<?php

declare(strict_types=1);

namespace Cel\Extension\Core\BinaryOperator\Handler\SubtractionOperator;

use Cel\Exception\InternalException;
use Cel\Exception\OverflowException;
use Cel\Operator\BinaryOperatorOverloadHandlerInterface;
use Cel\Syntax\Binary\BinaryExpression;
use Cel\Util\OperandUtil;
use Cel\Value\UnsignedIntegerValue;
use Cel\Value\Value;
use Override;

use function bccomp;
use function bcsub;

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
     * @throws OverflowException If subtraction would result in a negative value.
     */
    #[Override]
    public function __invoke(BinaryExpression $expression, Value $left, Value $right): Value
    {
        $left = OperandUtil::assertLeft($left, UnsignedIntegerValue::class);
        $right = OperandUtil::assertRight($right, UnsignedIntegerValue::class);

        $res = bcsub((string) $left->value, (string) $right->value);
        if (bccomp($res, '0') === -1) {
            throw new OverflowException(
                'Unsigned integer overflow on subtraction',
                $expression->left->getSpan()->join($expression->right->getSpan()),
            );
        }

        return new UnsignedIntegerValue($res);
    }
}
