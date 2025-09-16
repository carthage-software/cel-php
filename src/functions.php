<?php

declare(strict_types=1);

namespace Cel;

use Cel\Runtime\Configuration;
use Cel\Runtime\Value\Value;
use Psl\Dict;

/**
 * Runs a CEL expression with the given variables and configuration.
 *
 * @param string $expression expression to run.
 * @param array<string, mixed> $variables variables to bind in the environment.
 * @param Configuration $configuration runtime configuration.
 *
 * @return Value the resulting value of the expression.
 *
 * @throws Parser\Exception\ExceptionInterface on parse errors.
 * @throws Runtime\Exception\IncompatibleValueTypeException If a provided variable is of an unsupported type.
 * @throws Runtime\Exception\EvaluationException on runtime errors.
 */
function run(string $expression, array $variables = [], Configuration $configuration = new Configuration()): Value
{
    $parser = new Parser\Parser();
    $expression = $parser->parseString($expression);
    $runtime = new Runtime\Runtime($configuration);

    $environment = new Runtime\Environment\Environment(Dict\map($variables, Value::from(...)));

    return $runtime->run($expression, $environment)->result;
}
