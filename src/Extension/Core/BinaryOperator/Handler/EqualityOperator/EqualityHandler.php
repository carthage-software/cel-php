<?php

declare(strict_types=1);

namespace Cel\Extension\Core\BinaryOperator\Handler\EqualityOperator;

use Cel\Operator\BinaryOperatorOverloadHandlerInterface;
use Cel\Syntax\Binary\BinaryExpression;
use Cel\Value\BooleanValue;
use Cel\Value\Value;
use Override;

/**
 * Handles equality (`==`) and inequality (`!=`) between any two values.
 *
 * Equality is total: values of incompatible types are simply unequal rather than
 * producing an error. Numeric values (int, uint, double) compare across types on
 * a single number line, and `null` is equal only to `null`.
 *
 * @internal
 */
final readonly class EqualityHandler implements BinaryOperatorOverloadHandlerInterface
{
    public function __construct(
        private bool $isEqual,
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
        $equal = $left->isEqual($right);

        return new BooleanValue($this->isEqual ? $equal : !$equal);
    }
}
