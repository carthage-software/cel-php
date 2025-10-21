<?php

declare(strict_types=1);

namespace Cel\Exception;

use Cel\Span\Span;
use RuntimeException;
use Throwable;

/**
 * @consistent-constructor
 */
class EvaluationException extends RuntimeException implements ExceptionInterface
{
    public function __construct(
        string $message,
        public readonly Span $span,
        null|Throwable $previous = null,
    ) {
        parent::__construct($message, previous: $previous);
    }

    /**
     * Returns a new instance with the given span.
     */
    public function withSpan(Span $span): static
    {
        return new static($this->getMessage(), $span);
    }

    /**
     * Gets the span associated with the exception.
     */
    public function getSpan(): Span
    {
        return $this->span;
    }
}
