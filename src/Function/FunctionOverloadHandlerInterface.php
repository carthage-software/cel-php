<?php

declare(strict_types=1);

namespace Cel\Function;

use Cel\Span\Span;
use Cel\Value\Value;

/**
 * Interface for function overload handlers.
 *
 * Each handler implements a specific overload of a CEL function,
 * making the code more readable and testable.
 */
interface FunctionOverloadHandlerInterface
{
    /**
     * Handles the function call for this specific overload.
     *
     * @param Span $span The span covering the call expression.
     * @param list<Value> $arguments The evaluated arguments.
     *
     * @return Value The result of the function call.
     */
    public function __invoke(Span $span, array $arguments): Value;
}
