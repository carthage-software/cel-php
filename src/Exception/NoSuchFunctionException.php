<?php

declare(strict_types=1);

namespace Cel\Exception;

/**
 * Thrown when a function is not defined in the environment.
 *
 * @api
 */
final class NoSuchFunctionException extends EvaluationException {}
