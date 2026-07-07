<?php

declare(strict_types=1);

namespace Cel\Exception;

use RuntimeException;

use function sprintf;

/**
 * Thrown when a string cannot be parsed as a number in a given base, either
 * because it contains a digit that is invalid for that base or because the
 * parsed value exceeds the representable integer range.
 *
 * @api
 */
final class NumberFormatException extends RuntimeException implements ExceptionInterface
{
    public static function forInvalidDigit(string $digit, int $base): self
    {
        return new self(sprintf('Invalid digit %s in base %d', $digit, $base));
    }

    public static function forOverflow(string $number, int $base): self
    {
        return new self(sprintf('Unexpected integer overflow parsing %s from base %d', $number, $base));
    }
}
