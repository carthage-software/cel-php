<?php

declare(strict_types=1);

namespace Cel;

use Cel\Exception\EvaluationException;
use Cel\Exception\IncompatibleValueTypeException;
use Cel\Exception\InternalException;
use Cel\Input\Input;
use Cel\Input\InputInterface;
use Cel\Parser\Exception\UnexpectedEndOfFileException;
use Cel\Parser\Exception\UnexpectedTokenException;
use Cel\Runtime\Configuration;
use Cel\Value\Value;

/**
 * Evaluates a CEL expression with the given variables and configuration.
 *
 * This is a convenience function that combines parsing, optimizing, and running
 * the expression in one step.
 *
 * @param InputInterface|string $expression expression to evaluate.
 * @param array<string, mixed> $variables variables to bind in the environment.
 * @param Configuration $configuration runtime configuration.
 *
 * @return Value the resulting value of the expression.
 *
 * @throws InternalException If an internal error occurs (e.g., cache serialization failure).
 * @throws UnexpectedEndOfFileException If the parser encounters an unexpected end of input.
 * @throws UnexpectedTokenException If the parser encounters an unexpected token.
 * @throws IncompatibleValueTypeException If a provided variable is of an unsupported type.
 * @throws EvaluationException If evaluation fails during runtime.
 */
function evaluate(
    InputInterface|string $expression,
    array $variables = [],
    Configuration $configuration = new Configuration(),
): Value {
    $cel = new Cel(runtime: new Runtime\Runtime(configuration: $configuration));

    // Ensure expression is an InputInterface
    $expression = $expression instanceof InputInterface ? $expression : new Input($expression);

    // Parse and optimize
    $parsedExpression = $cel->parse($expression);
    $parsedExpression = $cel->optimize($parsedExpression);

    // Evaluate expression with variables
    $receipt = $cel->run($parsedExpression, $variables);

    return $receipt->result;
}
