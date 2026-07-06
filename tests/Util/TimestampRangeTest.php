<?php

declare(strict_types=1);

namespace Cel\Tests\Util;

use Cel\Util\TimestampRange;
use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\TestCase;

final class TimestampRangeTest extends TestCase
{
    #[DataProvider('provideSecondsCases')]
    public function testIsValidSeconds(bool $expected, int $seconds): void
    {
        static::assertSame($expected, TimestampRange::isValidSeconds($seconds));
    }

    /**
     * @return iterable<string, array{bool, int}>
     */
    public static function provideSecondsCases(): iterable
    {
        yield 'the epoch is valid' => [true, 0];
        yield 'the minimum second is valid' => [true, TimestampRange::MIN_SECONDS];
        yield 'the maximum second is valid' => [true, TimestampRange::MAX_SECONDS];
        yield 'one second below the minimum is invalid' => [false, TimestampRange::MIN_SECONDS - 1];
        yield 'one second above the maximum is invalid' => [false, TimestampRange::MAX_SECONDS + 1];
    }
}
