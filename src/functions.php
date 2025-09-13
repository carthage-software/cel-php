<?php

declare(strict_types=1);

namespace Cel;

use Cel\Runtime\Value\Value;
use Psl\Dict;

/**
 * @param string $expression expression to run.
 * @param array<string, mixed> $variables variables to bind in the environment.
 *
 * @return Value the resulting value of the expression.
 *
 * @throws Parser\Exception\ExceptionInterface on parse errors.
 * @throws Runtime\Exception\IncompatibleValueTypeException If a provided variable is of an unsupported type.
 * @throws Runtime\Exception\RuntimeException on runtime errors.
 */
function run(string $expression, array $variables = []): Value
{
    $parser = new Parser\Parser();
    $expression = $parser->parseString($expression);
    $runtime = new Runtime\Runtime();

    return $runtime->run($expression, new Runtime\Environment\Environment(Dict\map($variables, Value::from(...))));
}
