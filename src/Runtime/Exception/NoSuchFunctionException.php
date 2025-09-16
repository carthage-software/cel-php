<?php

declare(strict_types=1);

namespace Cel\Runtime\Exception;

/**
 * Thrown when a function is not defined in the environment.
 */
final class NoSuchFunctionException extends EvaluationException
{
}
