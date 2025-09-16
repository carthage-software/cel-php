<?php

declare(strict_types=1);

namespace Cel;

use Cel\Input\InputInterface;
use Cel\Runtime\Configuration;
use Cel\Runtime\Value\Value;
use Psl\Dict;

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
 * @throws Parser\Exception\ExceptionInterface on parse errors.
 * @throws Runtime\Exception\IncompatibleValueTypeException If a provided variable is of an unsupported type.
 * @throws Runtime\Exception\EvaluationException If evaluation fails.
 */
function evaluate(
    InputInterface|string $expression,
    array $variables = [],
    Configuration $configuration = new Configuration(),
): Value {
    $cel = new CommonExpressionLanguage(runtime: new Runtime\Runtime(configuration: $configuration));

    $expression = $expression instanceof InputInterface ? $cel->parse($expression) : $cel->parseString($expression);
    $expression = $cel->optimize($expression);

    $environment = new Runtime\Environment\Environment(Dict\map($variables, Value::from(...)));

    return $cel->run($expression, $environment)->result;
}

/**
 * Alias for `evaluate()`.
 *
 * @param string $expression    expression to evaluate.
 * @param array<string, mixed>  $variables     variables to bind in the environment.
 * @param Configuration         $configuration runtime configuration.
 *
 * @return Value the resulting value of the expression.
 *
 * @throws Parser\Exception\ExceptionInterface on parse errors.
 * @throws Runtime\Exception\IncompatibleValueTypeException If a provided variable is of an unsupported type.
 * @throws Runtime\Exception\EvaluationException If evaluation fails.
 *
 * @see evaluate()
 * @deprecated use `evaluate()` instead.
 */
function run(string $expression, array $variables = [], Configuration $configuration = new Configuration()): Value
{
    return namespace\evaluate($expression, $variables, $configuration);
}
