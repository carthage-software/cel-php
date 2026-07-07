<?php

declare(strict_types=1);

namespace Cel\Extension\Core\BinaryOperator\Handler\InOperator;

use Cel\Exception\InternalException;
use Cel\Exception\UnsupportedOperationException;
use Cel\Operator\BinaryOperatorOverloadHandlerInterface;
use Cel\Syntax\Binary\BinaryExpression;
use Cel\Util\OperandUtil;
use Cel\Value\BooleanValue;
use Cel\Value\ListValue;
use Cel\Value\Value;
use Override;

use function array_any;

/**
 * @internal
 */
final readonly class BooleanListHandler implements BinaryOperatorOverloadHandlerInterface
{
    /**
     * @param BinaryExpression $expression The binary expression being evaluated.
     * @param Value $left The evaluated left operand.
     * @param Value $right The evaluated right operand.
     *
     * @return Value The result of the binary operation.
     *
     * @throws InternalException If operand type assertion fails.
     * @throws UnsupportedOperationException If a value comparison is not supported (e.g. NaN).
     */
    #[Override]
    public function __invoke(BinaryExpression $expression, Value $left, Value $right): Value
    {
        $left = OperandUtil::assertLeft($left, BooleanValue::class);
        $right = OperandUtil::assertRight($right, ListValue::class);

        return new BooleanValue(array_any(
            $right->value,
            /** @throws UnsupportedOperationException If a value comparison is not supported (e.g. NaN). */
            static fn(Value $item): bool => $item->isEqual($left),
        ));
    }
}
