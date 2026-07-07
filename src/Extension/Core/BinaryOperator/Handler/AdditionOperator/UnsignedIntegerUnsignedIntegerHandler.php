<?php

declare(strict_types=1);

namespace Cel\Extension\Core\BinaryOperator\Handler\AdditionOperator;

use Cel\Exception\InternalException;
use Cel\Exception\OverflowException;
use Cel\Operator\BinaryOperatorOverloadHandlerInterface;
use Cel\Syntax\Binary\BinaryExpression;
use Cel\Util\OperandUtil;
use Cel\Value\UnsignedIntegerValue;
use Cel\Value\Value;
use Override;

use function bcadd;
use function bccomp;

/**
 * @internal
 */
final readonly class UnsignedIntegerUnsignedIntegerHandler implements BinaryOperatorOverloadHandlerInterface
{
    private const string UINT64_MAX = '18446744073709551615';

    /**
     * @param BinaryExpression $expression The binary expression being evaluated.
     * @param Value $left The evaluated left operand.
     * @param Value $right The evaluated right operand.
     *
     * @return Value The result of the binary operation.
     *
     * @throws InternalException If operand type assertion fails.
     * @throws OverflowException If the sum exceeds the unsigned 64-bit range.
     */
    #[Override]
    public function __invoke(BinaryExpression $expression, Value $left, Value $right): Value
    {
        $left = OperandUtil::assertLeft($left, UnsignedIntegerValue::class);
        $right = OperandUtil::assertRight($right, UnsignedIntegerValue::class);

        $result = bcadd((string) $left->value, (string) $right->value);
        if (bccomp($result, self::UINT64_MAX) > 0) {
            throw new OverflowException(
                'Unsigned integer overflow on addition',
                $expression->left->getSpan()->join($expression->right->getSpan()),
            );
        }

        return new UnsignedIntegerValue($result);
    }
}
