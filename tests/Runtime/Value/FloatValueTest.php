<?php

declare(strict_types=1);

namespace Cel\Tests\Runtime\Value;

use Cel\Exception\UnsupportedOperationException;
use Cel\Value\BooleanValue;
use Cel\Value\FloatValue;
use Cel\Value\IntegerValue;
use Cel\Value\UnsignedIntegerValue;
use Cel\Value\Value;
use Cel\Value\ValueKind;
use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\TestCase;

final class FloatValueTest extends TestCase
{
    public function testConstructorAndGetters(): void
    {
        $value = new FloatValue(1.23);
        static::assertSame(1.23, $value->value);
        static::assertSame(1.23, $value->getRawValue());
        static::assertSame(ValueKind::Float, $value->getKind());
        static::assertSame('double', $value->getType());
    }

    #[DataProvider('provideEqualityCases')]
    public function testIsEqual(bool $expected, Value $a, Value $b): void
    {
        static::assertSame($expected, $a->isEqual($b));
    }

    /**
     * @return iterable<string, array{bool, FloatValue, Value}>
     */
    public static function provideEqualityCases(): iterable
    {
        yield 'float == float' => [true, new FloatValue(1.5), new FloatValue(1.5)];
        yield 'float != float' => [false, new FloatValue(1.5), new FloatValue(1.6)];
        yield 'float == int' => [true, new FloatValue(1.0), new IntegerValue(1)];
        yield 'float != int' => [false, new FloatValue(1.5), new IntegerValue(1)];
        yield 'float == uint' => [true, new FloatValue(1.0), new UnsignedIntegerValue(1)];
        yield 'float != uint' => [false, new FloatValue(1.5), new UnsignedIntegerValue(1)];
        yield 'float != bool' => [false, new FloatValue(1.0), new BooleanValue(true)];
    }

    #[DataProvider('provideComparisonCases')]
    public function testComparisons(Value $a, Value $b, bool $isLess, bool $isGreater): void
    {
        static::assertSame($isLess, $a->isLessThan($b));
        static::assertSame($isGreater, $a->isGreaterThan($b));
    }

    /**
     * @return iterable<string, array{FloatValue, Value, bool, bool}>
     */
    public static function provideComparisonCases(): iterable
    {
        yield '1.5 vs 1.6' => [new FloatValue(1.5), new FloatValue(1.6), true, false];
        yield '1.6 vs 1.5' => [new FloatValue(1.6), new FloatValue(1.5), false, true];
        yield '1.5 vs 1.5' => [new FloatValue(1.5), new FloatValue(1.5), false, false];
        yield '1.5 vs int 2' => [new FloatValue(1.5), new IntegerValue(2), true, false];
        yield '2.5 vs int 2' => [new FloatValue(2.5), new IntegerValue(2), false, true];
        yield '1.0 vs int 1' => [new FloatValue(1.0), new IntegerValue(1), false, false];
        yield '1.5 vs uint 2' => [new FloatValue(1.5), new UnsignedIntegerValue(2), true, false];
    }

    public function testIsLessThanThrowsOnNonNumericType(): void
    {
        $this->expectException(UnsupportedOperationException::class);
        $this->expectExceptionMessage('Cannot compare values of type `double` and `bool`');

        new FloatValue(1.0)->isLessThan(new BooleanValue(true));
    }

    public function testIsGreaterThanThrowsOnNonNumericType(): void
    {
        $this->expectException(UnsupportedOperationException::class);
        $this->expectExceptionMessage('Cannot compare values of type `double` and `bool`');

        new FloatValue(1.0)->isGreaterThan(new BooleanValue(true));
    }

    public function testNaNCannotBeOrdered(): void
    {
        $this->expectException(UnsupportedOperationException::class);
        $this->expectExceptionMessage('NaN values cannot be ordered');

        new FloatValue(NAN)->isLessThan(new FloatValue(1.0));
    }
}
