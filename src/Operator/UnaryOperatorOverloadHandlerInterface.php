<?php

declare(strict_types=1);

namespace Cel\Operator;

use Cel\Span\Span;
use Cel\Value\Value;

/**
 * Defines the contract for a unary operator overload handler.
 */
interface UnaryOperatorOverloadHandlerInterface
{
    /**
     * Handles the unary operation for this specific overload.
     *
     * @param Span $span The span covering the unary expression.
     * @param Value $operand The evaluated operand.
     *
     * @return Value The result of the unary operation.
     */
    public function __invoke(Span $span, Value $operand): Value;
}
