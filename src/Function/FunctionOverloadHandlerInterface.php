<?php

declare(strict_types=1);

namespace Cel\Function;

use Cel\Syntax\Member\CallExpression;
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
     * @param CallExpression $call The call expression being evaluated.
     * @param list<Value> $arguments The evaluated arguments.
     *
     * @return Value The result of the function call.
     */
    public function __invoke(CallExpression $call, array $arguments): Value;
}
