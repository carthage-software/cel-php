<?php

declare(strict_types=1);

namespace Cel\Operator;

use Cel\Syntax\Binary\BinaryExpression;
use Cel\Value\Value;

/**
 * Defines the contract for a binary operator overload handler.
 */
interface BinaryOperatorOverloadHandlerInterface
{
    /**
     * Handles the binary operation for this specific overload.
     *
     * @param BinaryExpression $expression The binary expression being evaluated.
     * @param Value $left The evaluated left operand.
     * @param Value $right The evaluated right operand.
     *
     * @return Value The result of the binary operation.
     */
    public function __invoke(BinaryExpression $expression, Value $left, Value $right): Value;
}
