<?php

declare(strict_types=1);

namespace Cel\Tests\Util;

use Cel\Exception\NumberFormatException;
use Cel\Util\NumberBase;
use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\TestCase;

use const PHP_INT_MAX;

final class NumberBaseTest extends TestCase
{
    #[DataProvider('provideFromBaseCases')]
    public function testFromBase(int $expected, string $number, int $fromBase): void
    {
        static::assertSame($expected, NumberBase::fromBase($number, $fromBase));
    }

    /**
     * @return iterable<string, array{int, string, int}>
     */
    public static function provideFromBaseCases(): iterable
    {
        yield 'zero' => [0, '0', 10];
        yield 'digit nine (upper edge of 0-9 range)' => [9, '9', 10];
        yield 'two decimal digits' => [10, '10', 10];
        yield 'hex ff' => [255, 'ff', 16];
        yield 'lowercase a (lower edge of a-z range)' => [10, 'a', 16];
        yield 'lowercase z (upper edge of a-z range)' => [35, 'z', 36];
        // Parsing is case-insensitive: A-Z alias a-z (ordinal - 55), so these pin the A-Z range.
        yield 'uppercase A (lower edge of A-Z range)' => [10, 'A', 16];
        yield 'uppercase Z (upper edge of A-Z range)' => [35, 'Z', 36];
        yield 'max int at the boundary' => [PHP_INT_MAX, '9223372036854775807', 10];
    }

    #[DataProvider('provideFromBaseInvalidCases')]
    public function testFromBaseRejectsInvalidDigit(string $number, int $fromBase): void
    {
        $this->expectException(NumberFormatException::class);

        NumberBase::fromBase($number, $fromBase);
    }

    /**
     * @return iterable<string, array{string, int}>
     */
    public static function provideFromBaseInvalidCases(): iterable
    {
        yield 'letter equal to base' => ['a', 10];
        yield 'hex g' => ['g', 16];
        yield 'punctuation' => ['!', 10];
        yield 'space' => [' ', 10];
    }

    #[DataProvider('provideFromBaseOverflowCases')]
    public function testFromBaseOverflows(string $number, int $fromBase): void
    {
        $this->expectException(NumberFormatException::class);

        NumberBase::fromBase($number, $fromBase);
    }

    /**
     * @return iterable<string, array{string, int}>
     */
    public static function provideFromBaseOverflowCases(): iterable
    {
        yield 'ten to the nineteenth' => ['10000000000000000000', 10];
        yield 'twenty nines' => ['99999999999999999999', 10];
    }

    #[DataProvider('provideToBaseCases')]
    public function testToBase(string $expected, int $number, int $base): void
    {
        static::assertSame($expected, NumberBase::toBase($number, $base));
    }

    /**
     * @return iterable<string, array{string, int, int}>
     */
    public static function provideToBaseCases(): iterable
    {
        yield 'zero' => ['0', 0, 10];
        yield 'single digit' => ['9', 9, 10];
        yield 'hex' => ['ff', 255, 16];
        yield 'binary' => ['1010', 10, 2];
        yield 'base 36 z' => ['z', 35, 36];
        yield 'base 62 Z' => ['Z', 61, 62];
    }

    #[DataProvider('provideBaseConvertCases')]
    public function testBaseConvert(string $expected, string $value, int $fromBase, int $toBase): void
    {
        static::assertSame($expected, NumberBase::baseConvert($value, $fromBase, $toBase));
    }

    /**
     * @return iterable<string, array{string, string, int, int}>
     */
    public static function provideBaseConvertCases(): iterable
    {
        yield 'zero' => ['0', '0', 10, 16];
        yield 'hex to decimal' => ['597', '255', 16, 10];
        yield 'hex to binary' => ['11111111', 'ff', 16, 2];
        yield 'decimal to decimal' => ['255', '255', 10, 10];
        yield 'two to the sixty-fourth' => ['10000000000000000', '18446744073709551616', 10, 16];
    }

    #[DataProvider('provideBaseConvertInvalidCases')]
    public function testBaseConvertRejectsInvalidDigit(string $value, int $fromBase, int $toBase): void
    {
        $this->expectException(NumberFormatException::class);

        NumberBase::baseConvert($value, $fromBase, $toBase);
    }

    /**
     * @return iterable<string, array{string, int, int}>
     */
    public static function provideBaseConvertInvalidCases(): iterable
    {
        yield 'letter in base ten' => ['a', 10, 16];
        yield 'two in binary' => ['2', 2, 10];
    }
}
