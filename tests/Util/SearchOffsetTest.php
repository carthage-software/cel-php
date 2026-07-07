<?php

declare(strict_types=1);

namespace Cel\Tests\Util;

use Cel\Util\SearchOffset;
use OutOfRangeException;
use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\TestCase;

final class SearchOffsetTest extends TestCase
{
    #[DataProvider('provideNormalizeCases')]
    public function testNormalize(int $offset, int $length, int $expected): void
    {
        static::assertSame($expected, SearchOffset::normalize($offset, $length));
    }

    /**
     * @return iterable<string, array{int, int, int}>
     */
    public static function provideNormalizeCases(): iterable
    {
        yield 'zero offset returns zero' => [0, 5, 0];
        yield 'positive within range' => [3, 5, 3];
        yield 'positive equal to length' => [5, 5, 5];
        yield 'negative counts back from the end' => [-2, 5, 3];
        yield 'negative equal to minus length' => [-5, 5, 0];
        yield 'negative one is last position' => [-1, 5, 4];
    }

    #[DataProvider('provideOutOfRangeCases')]
    public function testNormalizeThrowsWhenOutOfRange(int $offset, int $length): void
    {
        $this->expectException(OutOfRangeException::class);

        SearchOffset::normalize($offset, $length);
    }

    /**
     * @return iterable<string, array{int, int}>
     */
    public static function provideOutOfRangeCases(): iterable
    {
        yield 'one past the end' => [6, 5];
        yield 'far past the end' => [10, 5];
        yield 'one before the start' => [-6, 5];
        yield 'far before the start' => [-10, 5];
    }
}
