<?php

declare(strict_types=1);

namespace Cel\Input;

use Cel\Exception\InternalException;
use Cel\Input\Exception\OutOfBoundsException;
use Override;
use Psl\Exception\ExceptionInterface;
use Psl\Math;
use Psl\Str;
use Psl\Str\Byte;

use function ctype_space;
use function hash;

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
     *
     * @throws InternalException If the byte length calculation fails due to an internal error.
     */
    public function __construct(string $bytes, int $cursor = 0)
    {
        $this->bytes = $bytes;
        try {
            $this->length = Byte\length($this->bytes);
        } catch (ExceptionInterface $e) {
            throw InternalException::forMessage('Failed to calculate input length', $e);
        }
        $this->cursor = $cursor;
    }

    #[Override]
    public function isEmpty(): bool
    {
        return 0 === $this->length;
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
     *
     * @throws InternalException If the slice operation fails due to an internal error.
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

        try {
            return Byte\slice($this->bytes, $start, $offset);
        } catch (ExceptionInterface $e) {
            throw InternalException::forMessage('Failed to slice input in range', $e);
        }
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
     *
     * @throws InternalException If the consume operation fails due to an internal error.
     */
    #[Override]
    public function consume(int $count): string
    {
        $slice = $this->read($count);
        try {
            $this->skip(Byte\length($slice));
        } catch (ExceptionInterface $e) {
            throw InternalException::forMessage('Failed to get slice length during consume', $e);
        }
        return $slice;
    }

    /**
     * @throws InternalException If the slice operation fails due to an internal error.
     */
    #[Override]
    public function consumeRemaining(): string
    {
        try {
            $remaining = Byte\slice($this->bytes, $this->cursor);
        } catch (ExceptionInterface $e) {
            throw InternalException::forMessage('Failed to slice remaining input', $e);
        }
        $this->cursor = $this->length;
        return $remaining;
    }

    /**
     * @throws InternalException If the search or slice operation fails due to an internal error.
     */
    #[Override]
    public function consumeUntil(string $search, bool $ignoreCase = false): string
    {
        try {
            $function = $ignoreCase ? Byte\search_ci(...) : Byte\search(...);
            $position = $function($this->bytes, $search, $this->cursor);
            if (null === $position) {
                return $this->consumeRemaining();
            }

            $offset = $position - $this->cursor;
            if ($offset < 1) {
                return '';
            }

            $slice = Byte\slice($this->bytes, $this->cursor, $offset);
            $this->cursor = $position;

            return $slice;
        } catch (ExceptionInterface $e) {
            throw InternalException::forMessage('Failed to consume until search term', $e);
        }
    }

    /**
     * @throws InternalException If the search or slice operation fails due to an internal error.
     */
    #[Override]
    public function consumeThrough(string $search): string
    {
        try {
            $position = Byte\search($this->bytes, $search, $this->cursor);
            if (null === $position) {
                return $this->consumeRemaining();
            }

            $end_position = $position + Byte\length($search);
            /** @var int<1, max> $offset */
            $offset = $end_position - $this->cursor;

            $slice = Byte\slice($this->bytes, $this->cursor, $offset);
            $this->cursor = $end_position;

            return $slice;
        } catch (ExceptionInterface $e) {
            throw InternalException::forMessage('Failed to consume through search term', $e);
        }
    }

    /**
     * @throws InternalException If the slice operation fails due to an internal error.
     */
    #[Override]
    public function consumeWhiteSpace(): string
    {
        $start = $this->cursor;
        while ($this->cursor < $this->length && ctype_space($this->bytes[$this->cursor])) {
            $this->cursor++;
        }

        /** @var int<1, max> $offset */
        $offset = $this->cursor - $start;

        try {
            return Byte\slice($this->bytes, $start, $offset);
        } catch (ExceptionInterface $e) {
            throw InternalException::forMessage('Failed to slice whitespace', $e);
        }
    }

    /**
     * @param int<0, max> $count
     *
     * @throws InternalException If the slice operation fails due to an internal error.
     */
    #[Override]
    public function read(int $count): string
    {
        try {
            return Byte\slice($this->bytes, $this->cursor, $count);
        } catch (ExceptionInterface $e) {
            throw InternalException::forMessage('Failed to read from input', $e);
        }
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

    /**
     * @throws InternalException If the length or comparison operation fails due to an internal error.
     */
    #[Override]
    public function isAt(string $search, bool $ignoreCase = false): bool
    {
        try {
            $slice = $this->read(Byte\length($search));

            if ($ignoreCase) {
                return Byte\compare_ci($slice, $search) === 0;
            }

            return $slice === $search;
        } catch (ExceptionInterface $e) {
            throw InternalException::forMessage('Failed to check if input is at search term', $e);
        }
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

    #[Override]
    public function getHash(): string
    {
        return hash('xxh128', $this->bytes);
    }
}
