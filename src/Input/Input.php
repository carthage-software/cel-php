<?php

declare(strict_types=1);

namespace Cel\Input;

use Cel\Input\Exception\OutOfBoundsException;
use Override;
use Psl\Math;
use Psl\Str;
use Psl\Str\Byte;

use function ctype_space;

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
    private int $cursor;

    /**
     * @param string $bytes The input byte sequence.
     * @param int<0, max> $cursor The initial cursor position (default is 0).
     */
    public function __construct(string $bytes, int $cursor = 0)
    {
        $this->bytes = $bytes;
        $this->length = Byte\length($this->bytes);
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
        $start = Math\minva($from, $this->length);
        $end = Math\minva($to, $this->length);

        $offset = $end - $start;
        if ($offset < 1) {
            return '';
        }

        return Byte\slice($this->bytes, $start, $offset);
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
        $this->cursor = Math\minva($this->cursor + $count, $this->length);
    }

    /**
     * @param int<0, max> $count
     */
    #[Override]
    public function consume(int $count): string
    {
        $slice = $this->read($count);
        $this->skip(Byte\length($slice));
        return $slice;
    }

    #[Override]
    public function consumeRemaining(): string
    {
        $remaining = Byte\slice($this->bytes, $this->cursor);
        $this->cursor = $this->length;
        return $remaining;
    }

    #[Override]
    public function consumeUntil(string $search, bool $ignoreCase = false): string
    {
        $function = $ignoreCase ? Byte\search_ci(...) : Byte\search(...);
        $position = $function($this->bytes, $search, $this->cursor);
        if ($position === null) {
            return $this->consumeRemaining();
        }

        $offset = $position - $this->cursor;
        if ($offset < 1) {
            return '';
        }

        $slice = Byte\slice($this->bytes, $this->cursor, $offset);
        $this->cursor = $position;

        return $slice;
    }

    #[Override]
    public function consumeThrough(string $search): string
    {
        $position = Byte\search($this->bytes, $search, $this->cursor);
        if ($position === null) {
            return $this->consumeRemaining();
        }

        $end_position = $position + Byte\length($search);
        /** @var int<1, max> $offset */
        $offset = $end_position - $this->cursor;

        $slice = Byte\slice($this->bytes, $this->cursor, $offset);
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

        /** @var int<1, max> $offset */
        $offset = $this->cursor - $start;

        return Byte\slice($this->bytes, $start, $offset);
    }

    /**
     * @param int<0, max> $count
     */
    #[Override]
    public function read(int $count): string
    {
        return Byte\slice($this->bytes, $this->cursor, $count);
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
        $slice = $this->read(Byte\length($search));

        if ($ignoreCase) {
            return Byte\compare_ci($slice, $search) === 0;
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

        try {
            return Byte\slice($this->bytes, $from, $n);
        } catch (Str\Exception\OutOfBoundsException) {
            return '';
        }
    }
}
