<?php

declare(strict_types=1);

namespace Cel\Exception;

/**
 * Thrown when attempting to create a message with a type that does not exist.
 */
final class NoSuchTypeException extends EvaluationException
{
}
