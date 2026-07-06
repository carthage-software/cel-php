<?php

declare(strict_types=1);

namespace Cel\Util;

use DateTimeImmutable;
use DateTimeZone;
use Exception;
use Psl\DateTime\DateTime;
use Psl\DateTime\Timestamp;
use Psl\DateTime\Timezone;

use function ctype_digit;

/**
 * Resolves the CEL timezone argument accepted by the timestamp accessor
 * functions (`getHours(tz)`, `getDayOfMonth(tz)`, and friends).
 *
 * CEL accepts IANA zone names, including legacy aliases such as `US/Central`,
 * as well as fixed `±HH:MM` UTC offsets. The full IANA database (with its legacy
 * aliases) is only available through the native {@see DateTimeZone}, so the
 * offset for the relevant instant is resolved there and applied to the
 * timestamp; the resulting wall-clock is then read back in UTC.
 */
final readonly class TimezoneUtil
{
    private function __construct() {}

    /**
     * Returns the wall-clock {@see DateTime} of a timestamp within a timezone,
     * or null when the zone cannot be recognised.
     */
    public static function localize(Timestamp $timestamp, string $zone): null|DateTime
    {
        $offset = self::offsetSeconds($timestamp->getSeconds(), $zone);
        if (null === $offset) {
            return null;
        }

        $shifted = Timestamp::fromParts($timestamp->getSeconds() + $offset, $timestamp->getNanoseconds());

        return DateTime::fromTimestamp($shifted, Timezone::UTC);
    }

    /**
     * Resolves the (DST-aware) offset in seconds of a zone at a given instant.
     */
    private static function offsetSeconds(int $seconds, string $zone): null|int
    {
        // A bare `HH:MM` offset needs an explicit sign to be understood.
        if ('' !== $zone && ctype_digit($zone[0])) {
            $zone = '+' . $zone;
        }

        try {
            // The native classes are used deliberately: only they expose the full
            // IANA database (with legacy aliases) and offset resolution.
            $timezone = new DateTimeZone($zone); // @mago-expect lint:psl-datetime
            $instant = new DateTimeImmutable('@' . $seconds); // @mago-expect lint:psl-datetime
        } catch (Exception) {
            return null;
        }

        return $timezone->getOffset($instant);
    }
}
