<?php

declare(strict_types=1);

namespace Cel\Extension\Core\BinaryOperator\Handler\InOperator;

use Cel\Exception\InternalException;
use Cel\Operator\BinaryOperatorOverloadHandlerInterface;
use Cel\Syntax\Binary\BinaryExpression;
use Cel\Util\MapKeyUtil;
use Cel\Util\OperandUtil;
use Cel\Value\BooleanValue;
use Cel\Value\MapValue;
use Cel\Value\Value;
use Override;

/**
 * Handles `numeric in map`, testing whether a numeric value (int, uint, or double)
 * is a key of the map, with heterogeneous numeric key matching.
 */
final readonly class NumericMapHandler implements BinaryOperatorOverloadHandlerInterface
{
    /**
     * @param BinaryExpression $expression The binary expression being evaluated.
     * @param Value $left The evaluated left operand (the candidate key).
     * @param Value $right The evaluated right operand (the map).
     *
     * @return Value True if the map contains a matching key.
     *
     * @throws InternalException If operand type assertion fails.
     */
    #[Override]
    public function __invoke(BinaryExpression $expression, Value $left, Value $right): Value
    {
        $right = OperandUtil::assertRight($right, MapValue::class);

        $key = MapKeyUtil::resolve($left);

        return new BooleanValue(null !== $key && $right->has($key));
    }
}
