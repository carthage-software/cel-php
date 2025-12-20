<?php

declare(strict_types=1);

namespace Cel\Span;

use Override;
use Psl\Str;
use Stringable;

/**
 * Represents a segment of the source code, defined by a start and end position.
 *
 * Spans are used to associate tokens and AST nodes with their original location
 * in the source, which is crucial for error reporting and debugging.
 */
final readonly class Span implements Stringable
{
    /**
     * @param int<0, max> $start The starting byte offset of the span (inclusive).
     * @param int<0, max> $end The ending byte offset of the span (exclusive).
     */
    public function __construct(
        public int $start,
        public int $end,
    ) {}

    /**
     * Creates a zero-length span at the origin (0,0).
     */
    public static function zero(): self
    {
        return new self(0, 0);
    }

    /**
     * Checks if the span is a zero-length span at the origin (0,0).
     */
    public function isZero(): bool
    {
        return 0 === $this->start && 0 === $this->end;
    }

    /**
     * Joins this span with another span, creating a new span that covers both.
     *
     * The resulting span starts at the start of this span and ends at the end of the other span.
     *
     * @param Span $other The other span to join with.
     *
     * @return Span A new span that covers both spans.
     */
    public function join(Span $other): self
    {
        return new self(start: $this->start, end: $other->end);
    }

    /**
     * Checks if the given offset is within the span.
     */
    public function hasOffset(int $offset): bool
    {
        return $this->start <= $offset && $offset < $this->end;
    }

    /**
     * Gets the length of the span.
     */
    public function length(): int
    {
        return $this->end - $this->start;
    }

    /**
     * Checks if the span is completely after the given position.
     */
    public function isAfter(int $position): bool
    {
        return $this->start > $position;
    }

    /**
     * Checks if the span is completely before the given position.
     */
    public function isBefore(int $position): bool
    {
        return $this->end <= $position;
    }

    /**
     * @inheritDoc
     */
    #[Override]
    public function __toString(): string
    {
        return Str\format('[%d..%d]', $this->start, $this->end);
    }
}
