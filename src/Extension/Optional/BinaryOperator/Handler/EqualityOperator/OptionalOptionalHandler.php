<?php

declare(strict_types=1);

namespace Cel\Extension\Optional\BinaryOperator\Handler\EqualityOperator;

use Cel\Exception\InternalException;
use Cel\Operator\BinaryOperatorOverloadHandlerInterface;
use Cel\Syntax\Binary\BinaryExpression;
use Cel\Util\OperandUtil;
use Cel\Value\BooleanValue;
use Cel\Value\OptionalValue;
use Cel\Value\Value;
use Override;

/**
 * Handles equality (`==`) and inequality (`!=`) between two optional values.
 *
 * Two optionals are equal when both are empty, or both hold values that are
 * themselves equal.
 */
final readonly class OptionalOptionalHandler implements BinaryOperatorOverloadHandlerInterface
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
     *
     * @throws InternalException If operand type assertion fails.
     */
    #[Override]
    public function __invoke(BinaryExpression $expression, Value $left, Value $right): BooleanValue
    {
        $left = OperandUtil::assertLeft($left, OptionalValue::class);
        $right = OperandUtil::assertRight($right, OptionalValue::class);

        return new BooleanValue($this->isEqual ? $left->isEqual($right) : !$left->isEqual($right));
    }
}
