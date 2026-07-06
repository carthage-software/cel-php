<?php

declare(strict_types=1);

namespace Cel\Extension\DateTime\Function\Handler\TimestampFunction;

use Cel\Exception\EvaluationException;
use Cel\Exception\InternalException;
use Cel\Exception\TypeConversionException;
use Cel\Function\FunctionOverloadHandlerInterface;
use Cel\Syntax\Member\CallExpression;
use Cel\Util\ArgumentsUtil;
use Cel\Util\TimestampRange;
use Cel\Value\StringValue;
use Cel\Value\TimestampValue;
use Cel\Value\Value;
use Override;
use Psl\DateTime;
use Psl\DateTime\Timestamp;
use Psl\Regex;
use Psl\Str;

use function checkdate;

/**
 * Parses an RFC3339 timestamp string.
 */
final readonly class FromStringHandler implements FunctionOverloadHandlerInterface
{
    private const string RFC3339_PATTERN = '/^(\d{4})-(\d{2})-(\d{2})T(\d{2}):(\d{2}):(\d{2})(?:\.(\d+))?(Z|[+-]\d{2}:\d{2})$/';

    /**
     * @param CallExpression $call The call expression.
     * @param list<Value> $arguments The function arguments.
     *
     * @return Value The resulting value.
     *
     * @throws EvaluationException If the operation fails.
     * @throws InternalException If an internal error occurs.
     */
    #[Override]
    public function __invoke(CallExpression $call, array $arguments): Value
    {
        $value = ArgumentsUtil::get($arguments, 0, StringValue::class);
        $string = $value->value;

        /** @var null|array{0: string, 1: string, 2: string, 3: string, 4: string, 5: string, 6: string, 7: string, 8: string} $matches */
        $matches = Regex\first_match($string, self::RFC3339_PATTERN);
        if (null === $matches) {
            throw new TypeConversionException(
                Str\format('Failed to parse timestamp string "%s".', $string),
                $call->getSpan(),
            );
        }

        $year = self::toInt($matches[1]);
        $month = self::toInt($matches[2]);
        $day = self::toInt($matches[3]);
        $hour = self::toInt($matches[4]);
        $minute = self::toInt($matches[5]);
        $second = self::toInt($matches[6]);

        // A year before 0001 is outside the representable range; report it as such
        // rather than as a malformed date (checkdate would reject year 0 outright).
        if ($year < 1) {
            throw new TypeConversionException(
                Str\format('Timestamp "%s" is outside the valid range.', $string),
                $call->getSpan(),
            );
        }

        if (!checkdate($month, $day, $year) || $hour > 23 || $minute > 59 || $second > 59) {
            throw new TypeConversionException(
                Str\format('Failed to parse timestamp string "%s".', $string),
                $call->getSpan(),
            );
        }

        $seconds =
            (self::daysFromCivil($year, $month, $day) * DateTime\SECONDS_PER_DAY)
                + ($hour * DateTime\SECONDS_PER_HOUR)
                + ($minute * DateTime\SECONDS_PER_MINUTE)
                + $second
            - self::timezoneOffsetSeconds($matches[8]);

        if (!TimestampRange::isValidSeconds($seconds)) {
            throw new TypeConversionException(
                Str\format('Timestamp "%s" is outside the valid range.', $string),
                $call->getSpan(),
            );
        }

        return new TimestampValue(Timestamp::fromParts($seconds, self::fractionToNanoseconds($matches[7])));
    }

    /**
     * Computes the number of days from the Unix epoch (1970-01-01) to the given
     * proleptic Gregorian date, using Howard Hinnant's algorithm.
     */
    private static function daysFromCivil(int $year, int $month, int $day): int
    {
        $year -= $month <= 2 ? 1 : 0;
        $era = (int) (($year >= 0 ? $year : $year - 399) / 400);
        $yearOfEra = $year - ($era * 400);
        $dayOfYear = (int) (((153 * ($month + ($month > 2 ? -3 : 9))) + 2) / 5) + $day - 1;
        $dayOfEra = ($yearOfEra * 365) + (int) ($yearOfEra / 4) - (int) ($yearOfEra / 100) + $dayOfYear;

        return ($era * 146_097) + $dayOfEra - 719_468;
    }

    /**
     * Converts an RFC3339 timezone designator (`Z` or `+-HH:MM`) to its offset
     * in seconds east of UTC.
     */
    private static function timezoneOffsetSeconds(string $timezone): int
    {
        if ('Z' === $timezone) {
            return 0;
        }

        $magnitude =
            (self::toInt(Str\slice($timezone, 1, 2)) * DateTime\SECONDS_PER_HOUR)
            + (self::toInt(Str\slice($timezone, 4, 2)) * DateTime\SECONDS_PER_MINUTE);

        return Str\starts_with($timezone, '-') ? -$magnitude : $magnitude;
    }

    /**
     * Normalizes a fractional-second string (the digits after the dot) to a
     * nanosecond count, padding or truncating to nanosecond precision.
     */
    private static function fractionToNanoseconds(string $fraction): int
    {
        if ('' === $fraction) {
            return 0;
        }

        return self::toInt(Str\slice(Str\pad_right($fraction, 9, '0'), 0, 9));
    }

    /**
     * Parses a run of decimal digits, tolerating leading zeros (unlike
     * `Str\to_int()`, which rejects them).
     */
    private static function toInt(string $digits): int
    {
        return (int) $digits;
    }
}
