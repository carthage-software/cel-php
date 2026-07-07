<?php

declare(strict_types=1);

namespace Cel\Util;

use Psl\DateTime\Duration;

use function abs;

/**
 * @internal
 */
final readonly class DurationRange
{
    /**
     * The maximum magnitude, in whole seconds, of a duration produced by
     * arithmetic. CEL durations are bounded by the signed 64-bit nanosecond
     * representation (as in cel-go), so a computed duration must fit within
     * roughly +-292 years; `floor((2^63 - 1) / 1e9)` seconds.
     */
    public const int MAX_SECONDS = 9_223_372_036;

    private function __construct() {}

    /**
     * Determines whether a duration is representable within the signed 64-bit
     * nanosecond range that CEL arithmetic is bounded by.
     */
    public static function isValid(Duration $duration): bool
    {
        return abs($duration->getTotalSeconds()) <= self::MAX_SECONDS;
    }
}
