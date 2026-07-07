<?php

declare(strict_types=1);

namespace Cel\Extension\Core\BinaryOperator\Handler\ComparisonOperator;

use Cel\Operator\BinaryOperatorOverloadHandlerInterface;
use Cel\Syntax\Binary\BinaryExpression;
use Cel\Value\BooleanValue;
use Cel\Value\Value;
use Override;

/**
 * Handles ordering comparisons (`<`, `<=`, `>`, `>=`) between any two numeric
 * values (int, uint, double), across types on a single number line.
 *
 * Comparisons involving NaN raise an error, as NaN cannot be ordered.
 *
 * @internal
 */
final readonly class NumericHandler implements BinaryOperatorOverloadHandlerInterface
{
    /**
     * @param callable(Value, Value): bool $comparator
     */
    public function __construct(
        private mixed $comparator,
    ) {}

    /**
     * @param BinaryExpression $expression The binary expression being evaluated.
     * @param Value $left The evaluated left operand.
     * @param Value $right The evaluated right operand.
     *
     * @return BooleanValue The result of the comparison.
     */
    #[Override]
    public function __invoke(BinaryExpression $expression, Value $left, Value $right): BooleanValue
    {
        return new BooleanValue(($this->comparator)($left, $right));
    }
}
