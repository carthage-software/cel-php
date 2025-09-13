<?php

declare(strict_types=1);

namespace Cel\Tests\Runtime\Extension;

use Cel\Runtime\Exception\RuntimeException;
use Cel\Runtime\Exception\TypeConversionException;
use Cel\Runtime\Extension\DateTime;
use Cel\Runtime\Value\DurationValue;
use Cel\Runtime\Value\IntegerValue;
use Cel\Runtime\Value\TimestampValue;
use Cel\Span\Span;
use Cel\Tests\Runtime\RuntimeTestCase;
use Override;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\Attributes\Medium;
use Psl\DateTime\Duration;
use Psl\DateTime\Timestamp;

#[CoversClass(DateTime\DateTimeExtension::class)]
#[CoversClass(DateTime\Function\DurationFunction::class)]
#[CoversClass(DateTime\Function\TimestampFunction::class)]
#[CoversClass(DateTime\Function\NowFunction::class)]
#[CoversClass(DateTime\Function\GetHoursFunction::class)]
#[CoversClass(DateTime\Function\GetMinutesFunction::class)]
#[CoversClass(DateTime\Function\GetSecondsFunction::class)]
#[CoversClass(DateTime\Function\GetMillisecondsFunction::class)]
#[CoversClass(DateTime\Function\GetFullYearFunction::class)]
#[CoversClass(DateTime\Function\GetMonthFunction::class)]
#[CoversClass(DateTime\Function\GetDayOfMonthFunction::class)]
#[CoversClass(DateTime\Function\GetDayOfYearFunction::class)]
#[CoversClass(DateTime\Function\GetDayOfWeekFunction::class)]
#[Medium]
final class DateTimeExtensionTest extends RuntimeTestCase
{
    #[Override]
    public static function provideEvaluationCases(): iterable
    {
        yield 'DateTime timestamp(): valid RFC3339 with milliseconds' =>
            [
                'timestamp("2025-09-13T12:30:05.123Z")',
                [],
                new TimestampValue(Timestamp::fromParts(1757766605, 123000000)),
            ];

        yield 'DateTime timestamp(): valid RFC3339 without milliseconds' =>
            [
                'timestamp("2025-09-13T12:30:05Z")',
                [],
                new TimestampValue(Timestamp::fromParts(1757766605)),
            ];

        yield 'DateTime timestamp(): invalid format' =>
            [
                'timestamp("2025-09-13 12:30:05")',
                [],
                new TypeConversionException('Failed to parse timestamp string "2025-09-13 12:30:05".', new Span(0, 30)),
            ];

        yield 'DateTime duration(): simple hours' =>
            [
                'duration("2h")',
                [],
                new DurationValue(Duration::hours(2)),
            ];

        yield 'DateTime duration(): complex' =>
            [
                'duration("1h30m5s")',
                [],
                new DurationValue(Duration::fromParts(1, 30, 5)),
            ];

        yield 'DateTime duration(): with fractional seconds' =>
            [
                'duration("1.5s")',
                [],
                new DurationValue(Duration::fromParts(0, 0, 1, 500_000_000)),
            ];

        yield 'DateTime duration(): negative' =>
            [
                'duration("-30m")',
                [],
                new DurationValue(Duration::minutes(-30)),
            ];

        yield 'DateTime duration(): invalid format' =>
            [
                'duration("1 year")',
                [],
                new TypeConversionException('Invalid duration format: "1 year"', new Span(0, 15)),
            ];

        // getHours()
        yield 'DateTime getHours(): duration' => ['getHours(duration("3h30m"))', [], new IntegerValue(3)];
        yield 'DateTime getHours(): timestamp UTC' =>
            ['getHours(timestamp("2025-09-13T10:20:30Z"))', [], new IntegerValue(10)];
        yield 'DateTime getHours(): timestamp with timezone' =>
            [
                'getHours(timestamp("2025-09-13T10:20:30Z"), "America/New_York")',
                [],
                new IntegerValue(6),
            ];

        // getMinutes()
        yield 'DateTime getMinutes(): duration' => ['getMinutes(duration("1h30m15s"))', [], new IntegerValue(90)];
        yield 'DateTime getMinutes(): timestamp UTC' =>
            ['getMinutes(timestamp("2025-09-13T10:20:30Z"))', [], new IntegerValue(20)];
        yield 'DateTime getMinutes(): timestamp with timezone' =>
            [
                'getMinutes(timestamp("2025-09-13T10:20:30Z"), "Europe/Paris")',
                [],
                new IntegerValue(20),
            ];

        // getSeconds()
        yield 'DateTime getSeconds(): duration' => ['getSeconds(duration("1m30.5s"))', [], new IntegerValue(90)];
        yield 'DateTime getSeconds(): timestamp UTC' =>
            ['timestamp("2025-09-13T10:20:30Z").getSeconds()', [], new IntegerValue(30)];
        yield 'DateTime getSeconds(): timestamp with timezone' =>
            [
                'timestamp("2025-09-13T10:20:30Z").getSeconds("Asia/Tokyo")',
                [],
                new IntegerValue(30),
            ];

        // getMilliseconds()
        yield 'DateTime getMilliseconds(): duration' =>
            ['duration("1.234s").getMilliseconds()', [], new IntegerValue(1234)];
        yield 'DateTime getMilliseconds(): timestamp UTC' =>
            ['timestamp("2025-09-13T10:20:30.456Z").getMilliseconds()', [], new IntegerValue(456)];
        yield 'DateTime getMilliseconds(): timestamp with timezone' =>
            [
                'timestamp("2025-09-13T10:20:30.789Z").getMilliseconds("Australia/Sydney")',
                [],
                new IntegerValue(789),
            ];

        // getFullYear()
        yield 'DateTime getFullYear(): timestamp UTC' =>
            ['getFullYear(timestamp("2025-09-13T10:00:00Z"))', [], new IntegerValue(2025)];
        yield 'DateTime getFullYear(): timestamp with timezone change' =>
            [
                'getFullYear(timestamp("2026-01-01T02:00:00Z"), "America/Los_Angeles")', // This is 2025-12-31 in LA
                [],
                new IntegerValue(2025),
            ];

        // getMonth()
        yield 'DateTime getMonth(): timestamp UTC (September is 8)' =>
            ['getMonth(timestamp("2025-09-13T10:00:00Z"))', [], new IntegerValue(8)];
        yield 'DateTime getMonth(): timestamp with timezone change (Feb is 1)' =>
            [
                'getMonth(timestamp("2025-03-01T02:00:00Z"), "America/Los_Angeles")', // This is Feb 28th in LA
                [],
                new IntegerValue(1),
            ];

        // getDayOfMonth()
        yield 'DateTime getDayOfMonth(): timestamp UTC' =>
            ['getDayOfMonth(timestamp("2025-09-13T10:00:00Z"))', [], new IntegerValue(13)];
        yield 'DateTime getDayOfMonth(): timestamp with timezone change' =>
            [
                'getDayOfMonth(timestamp("2025-09-01T02:00:00Z"), "America/Los_Angeles")', // This is Aug 31st in LA
                [],
                new IntegerValue(31),
            ];

        // getDayOfYear()
        yield 'DateTime getDayOfYear(): timestamp UTC' =>
            ['getDayOfYear(timestamp("2025-01-10T10:00:00Z"))', [], new IntegerValue(9)];
        yield 'DateTime getDayOfYear(): timestamp with timezone change' =>
            [
                'getDayOfYear(timestamp("2026-01-01T02:00:00Z"), "America/Los_Angeles")', // Day 364 of 2025 in LA
                [],
                new IntegerValue(364),
            ];

        // getDayOfWeek()
        yield 'DateTime getDayOfWeek(): Sunday is 0' =>
            ['getDayOfWeek(timestamp("2025-09-14T10:00:00Z"))', [], new IntegerValue(0)];
        yield 'DateTime getDayOfWeek(): Saturday is 6' =>
            ['getDayOfWeek(timestamp("2025-09-13T10:00:00Z"))', [], new IntegerValue(6)];
        yield 'DateTime getDayOfWeek(): timestamp with timezone change' =>
            [
                'getDayOfWeek(timestamp("2025-09-15T02:00:00Z"), "America/Los_Angeles")', // This is Sunday in LA
                [],
                new IntegerValue(0),
            ];

        // Error cases
        yield 'DateTime error: invalid timezone' =>
            [
                'getFullYear(timestamp("2025-09-13T10:20:30Z"), "Mars/Olympus_Mons")',
                [],
                new RuntimeException('getFullYear: timezone `Mars/Olympus_Mons` is not valid', new Span(0, 71)),
            ];
    }

    public function testNowFunctionReturnsCurrentTimestamp(): void
    {
        $before = Timestamp::now();
        $result = $this->evaluate('now()');
        $after = Timestamp::now();

        static::assertInstanceOf(TimestampValue::class, $result);
        static::assertTrue($result->value->afterOrAtTheSameTime($before));
        static::assertTrue($result->value->beforeOrAtTheSameTime($after));
    }
}
