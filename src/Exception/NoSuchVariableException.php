<?php

declare(strict_types=1);

namespace Cel\Exception;

/**
 * Thrown when a variable is not defined in the environment.
 *
 * @api
 */
final class NoSuchVariableException extends EvaluationException {}
