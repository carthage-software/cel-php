<?php

declare(strict_types=1);

namespace Cel\Util;

use OutOfRangeException;

use function sprintf;

/**
 * Normalizes a search offset the way the string functions expect: a negative
 * offset counts back from the end, and an offset outside the string is an error.
 *
 * @internal
 */
final readonly class SearchOffset
{
    private function __construct() {}

    /**
     * @throws OutOfRangeException If the offset falls outside the string.
     */
    public static function normalize(int $offset, int $length): int
    {
        $normalized = $offset < 0 ? $offset + $length : $offset;
        if ($normalized < 0 || $normalized > $length) {
            throw new OutOfRangeException(sprintf('Offset %d is out of bounds', $offset));
        }

        return $normalized;
    }
}
