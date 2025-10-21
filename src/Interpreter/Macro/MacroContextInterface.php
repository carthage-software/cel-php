<?php

declare(strict_types=1);

namespace Cel\Interpreter\Macro;

use Cel\Environment\EnvironmentInterface;
use Cel\Exception\EvaluationException;
use Cel\Syntax\Expression;
use Cel\Value\Value;

/**
 * Provides context for macro execution.
 *
 * This interface gives macros access to the interpreter's capabilities
 * without exposing the entire Interpreter class, following the principle
 * of least privilege.
 */
interface MacroContextInterface
{
    /**
     * Evaluates an expression and returns the result.
     *
     * @param Expression $expression The expression to evaluate
     *
     * @return Value The result of evaluation
     *
     * @throws EvaluationException If evaluation fails
     */
    public function evaluate(Expression $expression): Value;

    /**
     * Gets the current environment.
     *
     * @return EnvironmentInterface The current environment
     */
    public function getEnvironment(): EnvironmentInterface;

    /**
     * Sets a temporary environment for the duration of a callback.
     *
     * This is useful for macros that need to create a new scope with
     * additional variables.
     *
     * @param EnvironmentInterface $environment The environment to use
     * @param callable $callback The callback to execute
     *
     * @return mixed The return value of the callback
     */
    public function withEnvironment(EnvironmentInterface $environment, callable $callback): mixed;
}
