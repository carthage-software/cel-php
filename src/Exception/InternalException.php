<?php

declare(strict_types=1);

namespace Cel\Exception;

use LogicException;
use Psl\Str;
use Throwable;

/**
 * Exception thrown when an internal invariant is violated.
 *
 * This exception indicates a programming error within the CEL library itself,
 * not a user error. If you encounter this exception, it likely indicates a bug
 * that should be reported.
 */
final class InternalException extends LogicException implements ExceptionInterface
{
    public static function forInvalidOperator(string $operator): self
    {
        return new self(Str\format('Invalid operator: %s', $operator));
    }

    public static function forMessage(string $message, null|Throwable $previous = null): self
    {
        return new self($message, previous: $previous);
    }
}
