<?php

declare(strict_types=1);

namespace Cel\Operator;

use Cel\Syntax\Unary\UnaryExpression;
use Cel\Value\Value;

/**
 * Defines the contract for a unary operator overload handler.
 */
interface UnaryOperatorOverloadHandlerInterface
{
    /**
     * Handles the unary operation for this specific overload.
     *
     * @param UnaryExpression $expression The unary expression being evaluated.
     * @param Value $operand The evaluated operand.
     *
     * @return Value The result of the unary operation.
     */
    public function __invoke(UnaryExpression $expression, Value $operand): Value;
}
