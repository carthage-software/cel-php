<?php

declare(strict_types=1);

namespace Cel\Interpreter\Macro;

use Cel\Exception\EvaluationException;
use Cel\Exception\InvalidMacroCallException;
use Cel\Syntax\Member\CallExpression;
use Cel\Value\Value;

/**
 * Represents a CEL macro that can be evaluated.
 *
 * Macros are special functions that have access to the unevaluated AST
 * and can implement custom evaluation logic. Examples include `has()`,
 * `all()`, `exists()`, etc.
 */
interface MacroInterface
{
    /**
     * Gets the name of the macro.
     */
    public function getName(): string;

    /**
     * Determines if this macro can handle the given call expression.
     *
     * This allows macros to have specific requirements about their invocation context
     * (e.g., method call vs. function call, argument count, etc.)
     *
     * @param CallExpression $call The call expression to check
     *
     * @return bool True if this macro can handle the call, false otherwise
     */
    public function canHandle(CallExpression $call): bool;

    /**
     * Executes the macro and returns the result.
     *
     * @param CallExpression $call The call expression to execute
     * @param MacroContextInterface $context The execution context
     *
     * @return Value The result of executing the macro
     *
     * @throws InvalidMacroCallException If the macro call is invalid
     * @throws EvaluationException If evaluation fails
     */
    public function execute(CallExpression $call, MacroContextInterface $context): Value;
}
