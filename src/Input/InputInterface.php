<?php

declare(strict_types=1);

namespace Cel\Input;

use Cel\Common\HasCursorInterface;
use Cel\Exception\InternalException;

/**
 * An interface for a lexer input stream.
 *
 * Provides methods to read, peek, consume, and skip characters from an input
 * string while keeping track of the current position. This is designed to
 * mimic the behavior of a byte-oriented stream, similar to its Rust counterpart.
 */
interface InputInterface extends HasCursorInterface
{
    /**
     * Checks if the input string is completely empty.
     */
    public function isEmpty(): bool;

    /**
     * Returns the total length in bytes of the input string.
     *
     * @return int<0, max> The length of the input string in bytes.
     */
    public function length(): int;

    /**
     * Returns a slice of the input within a specified absolute range.
     *
     * This is useful for retrieving the raw text of a span or token whose
     * positions are absolute, even when the Input instance only contains a
     * subsection of the original source file. The slice is calculated
     * relative to a potential base offset the Input was initialized with.
     *
     * The returned slice is defensively clamped to the bounds of the current
     * Input's string to prevent errors.
     *
     * @param int<0, max> $from The absolute starting byte offset.
     * @param int<0, max> $to   The absolute ending byte offset (exclusive).
     *
     * @return string A string slice corresponding to the requested range.
     *
     * @throws InternalException If the slice operation fails due to an internal error.
     */
    public function sliceInRange(int $from, int $to): string;

    /**
     * Advances the internal cursor by one byte.
     *
     * If the end of input is reached, no action is taken.
     */
    public function next(): void;

    /**
     * Advances the internal cursor by the given number of bytes.
     * The cursor will not be moved past the end of the input.
     *
     * @param int<0, max> $count The number of bytes to skip.
     */
    public function skip(int $count): void;

    /**
     * Consumes the next number of bytes and returns them as a string.
     * Advances the cursor by the same number of bytes.
     *
     * @param int<0, max> $count The number of bytes to consume.
     *
     * @return string A string containing the consumed bytes.
     *
     * @throws InternalException If the consume operation fails due to an internal error.
     */
    public function consume(int $count): string;

    /**
     * Consumes all remaining bytes from the current position to the end of the input.
     * Advances the cursor to the end.
     *
     * @return string A string containing all remaining bytes.
     *
     * @throws InternalException If the slice operation fails due to an internal error.
     */
    public function consumeRemaining(): string;

    /**
     * Consumes bytes until the given search string is found.
     *
     * Advances the cursor to the start of the search string if found,
     * or to the end of the input if not found. The search string itself is NOT consumed.
     *
     * @param string $search     The string to search for.
     * @param bool   $ignoreCase Whether to perform a case-insensitive search.
     *
     * @return string A string containing the consumed bytes.
     *
     * @throws InternalException If the search or slice operation fails due to an internal error.
     */
    public function consumeUntil(string $search, bool $ignoreCase = false): string;

    /**
     * Consumes bytes until and *including* the given search string.
     *
     * Advances the cursor to the position right after the search string if found,
     * or to the end of the input if not found.
     *
     * @param string $search The string to search for.
     *
     * @return string A string containing the consumed bytes (including the search string).
     *
     * @throws InternalException If the search or slice operation fails due to an internal error.
     */
    public function consumeThrough(string $search): string;

    /**
     * Consumes ASCII whitespace characters from the current position.
     * Advances the cursor past the consumed whitespace.
     *
     * @return string A string containing the consumed whitespace characters.
     *
     * @throws InternalException If the slice operation fails due to an internal error.
     */
    public function consumeWhiteSpace(): string;

    /**
     * Reads the next number of bytes without advancing the cursor.
     *
     * @param int<0, max> $count The number of bytes to read.
     *
     * @return string A string containing the next bytes from the current position.
     *
     * @throws InternalException If the slice operation fails due to an internal error.
     */
    public function read(int $count): string;

    /**
     * Reads a single byte at a specific absolute offset within the input string
     * and returns its ordinal value, without advancing the internal cursor.
     *
     * @param int<0, max> $offset The zero-based byte offset from which to read.
     *
     * @return string The byte at the specified offset.
     *
     * @throws Exception\OutOfBoundsException if the offset is out of bounds.
     */
    public function readAt(int $offset): string;

    /**
     * Checks if the input at the current cursor position starts with the given string.
     *
     * @param string $search     The string to compare against the input.
     * @param bool   $ignoreCase Whether to perform a case-insensitive comparison.
     *
     * @return bool True if the next bytes match the search string.
     *
     * @throws InternalException If the length or comparison operation fails due to an internal error.
     */
    public function isAt(string $search, bool $ignoreCase = false): bool;

    /**
     * Peeks ahead from the current cursor position and reads a number of bytes
     * without advancing the cursor.
     *
     * @param int<0, max> $offset The number of bytes to skip before reading.
     * @param int<0, max> $n      The number of bytes to read after skipping.
     *
     * @return string A string containing the peeked bytes.
     */
    public function peek(int $offset, int $n): string;

    /**
     * Returns a hash of the input source suitable for use as a cache key.
     *
     * @return string A hash string (e.g., using xxh128 or similar fast hash algorithm)
     */
    public function getHash(): string;
}
