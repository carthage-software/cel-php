<?php

declare(strict_types=1);

namespace Cel\Interpreter;

use Cel\Environment\EnvironmentInterface;
use Cel\Runtime\Exception;
use Cel\Syntax\Expression;
use Cel\Value\Value;

interface InterpreterInterface
{
    /**
     * Gets the current environment.
     */
    public function getEnvironment(): EnvironmentInterface;

    /**
     * Evaluates the given expression and returns the resulting value.
     *
     * @param Expression $expression The expression to evaluate.
     *
     * @return Value The result of the evaluation.
     *
     * @throws Exception\EvaluationException on runtime errors.
     */
    public function run(Expression $expression): Value;

    /**
     * Indicates whether the last evaluated expression was idempotent.
     *
     * @return bool True if the last expression was idempotent, false otherwise.
     */
    public function wasIdempotent(): bool;

    /**
     * Resets the interpreter state, clearing any cached values or stateful information.
     */
    public function reset(): void;
}
