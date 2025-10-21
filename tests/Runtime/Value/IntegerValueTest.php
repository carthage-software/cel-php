<?php

declare(strict_types=1);

namespace Cel\Tests\Runtime\Value;

use Cel\Exception\UnsupportedOperationException;
use Cel\Value\FloatValue;
use Cel\Value\IntegerValue;
use Cel\Value\StringValue;
use Cel\Value\UnsignedIntegerValue;
use Cel\Value\ValueKind;
use PHPUnit\Framework\TestCase;

final class IntegerValueTest extends TestCase
{
    public function testValue(): void
    {
        $value = new IntegerValue(123);
        static::assertSame(123, $value->getRawValue());
        static::assertSame('int', $value->getType());
    }

    public function testGetKind(): void
    {
        $value = new IntegerValue(42);
        static::assertSame(ValueKind::Integer, $value->getKind());
    }

    public function testIsEqualWithSameInteger(): void
    {
        $val1 = new IntegerValue(42);
        $val2 = new IntegerValue(42);

        static::assertTrue($val1->isEqual($val2));
    }

    public function testIsEqualWithDifferentInteger(): void
    {
        $val1 = new IntegerValue(42);
        $val2 = new IntegerValue(43);

        static::assertFalse($val1->isEqual($val2));
    }

    public function testIsEqualWithFloatValue(): void
    {
        $int = new IntegerValue(42);
        $float = new FloatValue(42.0);

        static::assertTrue($int->isEqual($float));
    }

    public function testIsEqualWithDifferentFloatValue(): void
    {
        $int = new IntegerValue(42);
        $float = new FloatValue(42.5);

        static::assertFalse($int->isEqual($float));
    }

    public function testIsEqualWithUnsignedIntegerValue(): void
    {
        $int = new IntegerValue(42);
        $uint = new UnsignedIntegerValue(42);

        static::assertTrue($int->isEqual($uint));
    }

    public function testIsEqualWithDifferentUnsignedIntegerValue(): void
    {
        $int = new IntegerValue(42);
        $uint = new UnsignedIntegerValue(43);

        static::assertFalse($int->isEqual($uint));
    }

    public function testIsEqualWithNonNumericValueReturnsFalse(): void
    {
        $int = new IntegerValue(42);
        $string = new StringValue('42');

        static::assertFalse($int->isEqual($string));
    }

    public function testIsGreaterThan(): void
    {
        $val1 = new IntegerValue(43);
        $val2 = new IntegerValue(42);

        static::assertTrue($val1->isGreaterThan($val2));
        static::assertFalse($val2->isGreaterThan($val1));
    }

    public function testIsLessThan(): void
    {
        $val1 = new IntegerValue(42);
        $val2 = new IntegerValue(43);

        static::assertTrue($val1->isLessThan($val2));
        static::assertFalse($val2->isLessThan($val1));
    }

    public function testIsGreaterThanThrowsExceptionWithNonIntegerValue(): void
    {
        $int = new IntegerValue(42);
        $string = new StringValue('42');

        $this->expectException(UnsupportedOperationException::class);
        $int->isGreaterThan($string);
    }

    public function testIsLessThanThrowsExceptionWithNonIntegerValue(): void
    {
        $int = new IntegerValue(42);
        $string = new StringValue('42');

        $this->expectException(UnsupportedOperationException::class);
        $int->isLessThan($string);
    }
}
