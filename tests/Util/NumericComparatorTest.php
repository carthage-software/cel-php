<?php

declare(strict_types=1);

namespace Cel\Tests\Util;

use Cel\Exception\UnsupportedOperationException;
use Cel\Util\NumericComparator;
use Cel\Value\BooleanValue;
use Cel\Value\FloatValue;
use Cel\Value\IntegerValue;
use Cel\Value\StringValue;
use Cel\Value\UnsignedIntegerValue;
use Cel\Value\Value;
use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\TestCase;

use const PHP_INT_MAX;

final class NumericComparatorTest extends TestCase
{
    #[DataProvider('provideOrderCases')]
    public function testOrder(int $expected, Value $a, Value $b): void
    {
        static::assertSame($expected, NumericComparator::order($a, $b));
    }

    /**
     * @return iterable<string, array{int, Value, Value}>
     */
    public static function provideOrderCases(): iterable
    {
        // int vs int
        yield 'int < int' => [-1, new IntegerValue(1), new IntegerValue(2)];
        yield 'int > int' => [1, new IntegerValue(2), new IntegerValue(1)];
        yield 'int == int' => [0, new IntegerValue(1), new IntegerValue(1)];
        yield 'negative int vs int' => [-1, new IntegerValue(-5), new IntegerValue(3)];

        // int vs uint
        yield 'int < uint' => [-1, new IntegerValue(1), new UnsignedIntegerValue(2)];
        yield 'negative int < uint' => [-1, new IntegerValue(-1), new UnsignedIntegerValue(1)];
        yield 'uint > int' => [1, new UnsignedIntegerValue(2), new IntegerValue(1)];
        yield 'uint == uint' => [0, new UnsignedIntegerValue(4), new UnsignedIntegerValue(4)];

        // int vs double
        yield 'int < double' => [-1, new IntegerValue(1), new FloatValue(1.5)];
        yield 'int > double' => [1, new IntegerValue(2), new FloatValue(1.5)];
        yield 'int == double' => [0, new IntegerValue(1), new FloatValue(1.0)];

        // double vs int
        yield 'double < int' => [-1, new FloatValue(1.5), new IntegerValue(2)];
        yield 'double > int' => [1, new FloatValue(2.5), new IntegerValue(2)];
        yield 'double == int' => [0, new FloatValue(1.0), new IntegerValue(1)];

        // uint vs double and double vs uint
        yield 'uint < double' => [-1, new UnsignedIntegerValue(1), new FloatValue(1.5)];
        yield 'double < uint' => [-1, new FloatValue(1.5), new UnsignedIntegerValue(2)];
        yield 'double == uint' => [0, new FloatValue(3.0), new UnsignedIntegerValue(3)];

        // double vs double
        yield 'double < double' => [-1, new FloatValue(1.5), new FloatValue(1.6)];
        yield 'double > double' => [1, new FloatValue(1.6), new FloatValue(1.5)];
        yield 'double == double' => [0, new FloatValue(1.5), new FloatValue(1.5)];

        // range boundaries: double outside the int64 range
        yield 'double above int64 max vs int' => [1, new FloatValue(1.0e19), new IntegerValue(0)];
        yield 'double below int64 min vs int' => [-1, new FloatValue(-1.0e19), new IntegerValue(0)];
        yield 'int vs double above int64 max' => [-1, new IntegerValue(0), new FloatValue(1.0e19)];

        // range boundaries: double outside the uint64 range
        yield 'double above uint64 max vs uint' => [1, new FloatValue(1.0e20), new UnsignedIntegerValue(0)];
        yield 'negative double vs uint' => [-1, new FloatValue(-1.0), new UnsignedIntegerValue(0)];
        yield 'uint vs double above uint64 max' => [-1, new UnsignedIntegerValue(0), new FloatValue(1.0e20)];

        // large integers compared exactly as decimal strings
        yield 'int max vs uint via string compare' => [
            -1,
            new IntegerValue(PHP_INT_MAX),
            new UnsignedIntegerValue('18446744073709551615'),
        ];
    }

    #[DataProvider('provideEqualsCases')]
    public function testEquals(bool $expected, Value $a, Value $b): void
    {
        static::assertSame($expected, NumericComparator::equals($a, $b));
    }

    /**
     * @return iterable<string, array{bool, Value, Value}>
     */
    public static function provideEqualsCases(): iterable
    {
        yield 'int equals double' => [true, new IntegerValue(1), new FloatValue(1.0)];
        yield 'int equals uint' => [true, new IntegerValue(1), new UnsignedIntegerValue(1)];
        yield 'double equals uint' => [true, new FloatValue(2.0), new UnsignedIntegerValue(2)];
        yield 'int not equal double' => [false, new IntegerValue(1), new FloatValue(1.5)];
        yield 'NaN not equal double' => [false, new FloatValue(NAN), new FloatValue(1.0)];
        yield 'double not equal NaN' => [false, new FloatValue(1.0), new FloatValue(NAN)];
        yield 'NaN not equal NaN' => [false, new FloatValue(NAN), new FloatValue(NAN)];
    }

    public function testOrderThrowsOnNaNLeft(): void
    {
        $this->expectException(UnsupportedOperationException::class);
        $this->expectExceptionMessage('NaN values cannot be ordered');

        NumericComparator::order(new FloatValue(NAN), new IntegerValue(1));
    }

    public function testOrderThrowsOnNaNRight(): void
    {
        $this->expectException(UnsupportedOperationException::class);
        $this->expectExceptionMessage('NaN values cannot be ordered');

        NumericComparator::order(new IntegerValue(1), new FloatValue(NAN));
    }

    #[DataProvider('provideIsNumericCases')]
    public function testIsNumeric(bool $expected, Value $value): void
    {
        static::assertSame($expected, NumericComparator::isNumeric($value));
    }

    /**
     * @return iterable<string, array{bool, Value}>
     */
    public static function provideIsNumericCases(): iterable
    {
        yield 'int is numeric' => [true, new IntegerValue(1)];
        yield 'uint is numeric' => [true, new UnsignedIntegerValue(1)];
        yield 'double is numeric' => [true, new FloatValue(1.0)];
        yield 'string is not numeric' => [false, new StringValue('1')];
        yield 'bool is not numeric' => [false, new BooleanValue(true)];
    }

    #[DataProvider('provideIsNaNCases')]
    public function testIsNaN(bool $expected, Value $value): void
    {
        static::assertSame($expected, NumericComparator::isNaN($value));
    }

    /**
     * @return iterable<string, array{bool, Value}>
     */
    public static function provideIsNaNCases(): iterable
    {
        yield 'NaN double' => [true, new FloatValue(NAN)];
        yield 'finite double' => [false, new FloatValue(1.0)];
        yield 'int is not NaN' => [false, new IntegerValue(1)];
    }
}
