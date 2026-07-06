<?php

declare(strict_types=1);

namespace Cel\Util;

final readonly class TimestampRange
{
    /** Seconds at `0001-01-01T00:00:00Z`. */
    public const int MIN_SECONDS = -62_135_596_800;

    /** Seconds at `9999-12-31T23:59:59Z` (the final whole second in range). */
    public const int MAX_SECONDS = 253_402_300_799;

    private function __construct() {}

    /**
     * Determines whether a Unix-second value falls within the CEL timestamp range.
     */
    public static function isValidSeconds(int $seconds): bool
    {
        return $seconds >= self::MIN_SECONDS && $seconds <= self::MAX_SECONDS;
    }
}
