<?php

declare(strict_types=1);

namespace Cel\Runtime\Exception;

use Cel\Span\Span;

/**
 * @consistent-constructor
 */
class RuntimeException extends \RuntimeException implements ExceptionInterface
{
    public function __construct(
        string $message,
        public readonly Span $span,
    ) {
        parent::__construct($message);
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
