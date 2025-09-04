<?php

declare(strict_types=1);

namespace Cel\Input;

use Cel\Input\Exception\OutOfBoundsException;
use Override;

use function ctype_space;
use function min;
use function strcasecmp;
use function strlen;
use function strpos;
use function substr;

/**
 * A concrete implementation of {@see InputInterface} that operates on a string of bytes.
 */
final class Input implements InputInterface
{
    /**
     * @var string The raw byte string being processed.
     */
    private readonly string $bytes;

    /**
     * @var int<0, max> The total length of the byte string.
     */
    private readonly int $length;

    /**
     * @var int<0, max> The current position of the cursor within the byte string.
     */
    private int $cursor = 0;

    /**
     * @param string $bytes The input byte sequence.
     * @param int<0, max> $cursor The initial cursor position (default is 0).
     */
    public function __construct(string $bytes, int $cursor = 0)
    {
        $this->bytes = $bytes;
        $this->length = strlen($this->bytes);
        $this->cursor = $cursor;
    }

    #[Override]
    public function isEmpty(): bool
    {
        return $this->length === 0;
    }

    /**
     * @return int<0, max> The total length of the input byte sequence.
     */
    #[Override]
    public function length(): int
    {
        return $this->length;
    }

    /**
     * @return int<0, max> The current cursor position within the input byte sequence.
     */
    #[Override]
    public function cursorPosition(): int
    {
        return $this->cursor;
    }

    #[Override]
    public function hasReachedEnd(): bool
    {
        return $this->cursor >= $this->length;
    }

    /**
     * @param int<0, max> $from
     * @param int<0, max> $to
     */
    #[Override]
    public function sliceInRange(int $from, int $to): string
    {
        $start = min($from, $this->length);
        $end = min($to, $this->length);

        if ($start >= $end) {
            return '';
        }

        return substr($this->bytes, $start, $end - $start);
    }

    #[Override]
    public function next(): void
    {
        if ($this->cursor < $this->length) {
            $this->cursor++;
        }
    }

    /**
     * @param int<0, max> $count
     */
    #[Override]
    public function skip(int $count): void
    {
        $this->cursor = min($this->cursor + $count, $this->length);
    }

    /**
     * @param int<0, max> $count
     */
    #[Override]
    public function consume(int $count): string
    {
        $substring = $this->read($count);
        $this->skip(strlen($substring));
        return $substring;
    }

    #[Override]
    public function consumeRemaining(): string
    {
        $remaining = substr($this->bytes, $this->cursor);
        $this->cursor = $this->length;
        return $remaining;
    }

    #[Override]
    public function consumeUntil(string $search, bool $ignoreCase = false): string
    {
        $function = $ignoreCase ? stripos(...) : strpos(...);
        $position = $function($this->bytes, $search, $this->cursor);

        if ($position === false) { // @mago-expect lint:no-boolean-literal-comparison
            return $this->consumeRemaining();
        }

        $slice = substr($this->bytes, $this->cursor, $position - $this->cursor);
        $this->cursor = $position;

        return $slice;
    }

    #[Override]
    public function consumeThrough(string $search): string
    {
        $position = strpos($this->bytes, $search, $this->cursor);
        if ($position === false) { // @mago-expect lint:no-boolean-literal-comparison
            return $this->consumeRemaining();
        }

        $end_position = $position + strlen($search);
        $slice = substr($this->bytes, $this->cursor, $end_position - $this->cursor);
        $this->cursor = $end_position;

        return $slice;
    }

    #[Override]
    public function consumeWhiteSpace(): string
    {
        $start = $this->cursor;
        while ($this->cursor < $this->length && ctype_space($this->bytes[$this->cursor])) {
            $this->cursor++;
        }

        return substr($this->bytes, $start, $this->cursor - $start);
    }

    /**
     * @param int<0, max> $count
     */
    #[Override]
    public function read(int $count): string
    {
        return substr($this->bytes, $this->cursor, $count);
    }

    /**
     * @param int<0, max> $offset
     */
    #[Override]
    public function readAt(int $offset): string
    {
        if ($offset >= $this->length) {
            throw new OutOfBoundsException('Offset is out of bounds.');
        }

        return $this->bytes[$offset];
    }

    #[Override]
    public function isAt(string $search, bool $ignoreCase = false): bool
    {
        $slice = $this->read(strlen($search));

        if ($ignoreCase) {
            return strcasecmp($slice, $search) === 0;
        }

        return $slice === $search;
    }

    /**
     * @param int<0, max> $offset
     * @param int<0, max> $n
     */
    #[Override]
    public function peek(int $offset, int $n): string
    {
        $from = $this->cursor + $offset;

        return substr($this->bytes, $from, $n);
    }
}
