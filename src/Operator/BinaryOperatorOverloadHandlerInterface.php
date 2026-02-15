<?php

declare(strict_types=1);

namespace Cel\Operator;

use Cel\Span\Span;
use Cel\Value\Value;

/**
 * Defines the contract for a binary operator overload handler.
 */
interface BinaryOperatorOverloadHandlerInterface
{
    /**
     * Handles the binary operation for this specific overload.
     *
     * @param Span $span The span covering the binary expression.
     * @param Value $left The evaluated left operand.
     * @param Value $right The evaluated right operand.
     *
     * @return Value The result of the binary operation.
     */
    public function __invoke(Span $span, Value $left, Value $right): Value;
}
