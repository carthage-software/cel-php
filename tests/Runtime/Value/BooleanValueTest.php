<?php

declare(strict_types=1);

namespace Cel\Tests\Runtime\Value;

use Cel\Exception\UnsupportedOperationException;
use Cel\Value\BooleanValue;
use Cel\Value\IntegerValue;
use Cel\Value\Value;
use Cel\Value\ValueKind;
use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\TestCase;

final class BooleanValueTest extends TestCase
{
    public function testConstructorAndGetters(): void
    {
        $value = new BooleanValue(true);
        static::assertTrue($value->value);
        static::assertTrue($value->getRawValue());
        static::assertSame(ValueKind::Boolean, $value->getKind());
        static::assertSame('bool', $value->getType());
    }

    #[DataProvider('provideEqualityCases')]
    public function testIsEqual(bool $expected, Value $a, Value $b): void
    {
        static::assertSame($expected, $a->isEqual($b));
    }

    public static function provideEqualityCases(): iterable
    {
        yield 'true == true' => [true, new BooleanValue(true), new BooleanValue(true)];
        yield 'false == false' => [true, new BooleanValue(false), new BooleanValue(false)];
        yield 'true == false' => [false, new BooleanValue(true), new BooleanValue(false)];
        yield 'false == true' => [false, new BooleanValue(false), new BooleanValue(true)];
    }

    public function testIsEqualThrowsOnIncompatibleType(): void
    {
        $this->expectException(UnsupportedOperationException::class);
        $this->expectExceptionMessage('Cannot compare values of type `bool` and `int` for equality');

        new BooleanValue(true)->isEqual(new IntegerValue(1));
    }

    #[DataProvider('provideComparisonCases')]
    public function testComparisons(Value $a, Value $b, bool $isLess, bool $isGreater): void
    {
        static::assertSame($isLess, $a->isLessThan($b));
        static::assertSame($isGreater, $a->isGreaterThan($b));
    }

    public static function provideComparisonCases(): iterable
    {
        yield 'true vs false' => [new BooleanValue(true), new BooleanValue(false), false, true];
        yield 'false vs true' => [new BooleanValue(false), new BooleanValue(true), true, false];
        yield 'true vs true' => [new BooleanValue(true), new BooleanValue(true), false, false];
        yield 'false vs false' => [new BooleanValue(false), new BooleanValue(false), false, false];
    }

    public function testIsLessThanThrowsOnIncompatibleType(): void
    {
        $this->expectException(UnsupportedOperationException::class);
        $this->expectExceptionMessage('Cannot compare values of type `bool` and `int`');

        new BooleanValue(true)->isLessThan(new IntegerValue(1));
    }

    public function testIsGreaterThanThrowsOnIncompatibleType(): void
    {
        $this->expectException(UnsupportedOperationException::class);
        $this->expectExceptionMessage('Cannot compare values of type `bool` and `int`');

        new BooleanValue(true)->isGreaterThan(new IntegerValue(1));
    }
}
