<?php

declare(strict_types=1);

namespace Cel\Exception;

use Cel\Span\Span;
use Throwable;

/**
 * Thrown when division or modulo by zero is attempted.
 */
final class DivisionByZeroException extends EvaluationException
{
    public function __construct(string $message, null|Span $span = null, null|Throwable $previous = null)
    {
        parent::__construct($message, $span ?? Span::zero(), $previous);
    }
}
