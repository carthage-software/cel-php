<?php

declare(strict_types=1);

namespace Cel\Tests\Value;

use Cel\Exception\UnsupportedOperationException;
use Cel\Value\IntegerValue;
use Cel\Value\UnsignedIntegerValue;
use Cel\Value\ValueKind;
use PHPUnit\Framework\TestCase;

final class UnsignedIntegerValueTest extends TestCase
{
    public function testgetRawValue(): void
    {
        $value = new UnsignedIntegerValue(42);

        static::assertSame(42, $value->getRawValue());
    }

    public function testGetKind(): void
    {
        $value = new UnsignedIntegerValue(42);

        static::assertSame(ValueKind::UnsignedInteger, $value->getKind());
    }

    public function testGetType(): void
    {
        $value = new UnsignedIntegerValue(42);

        static::assertSame('uint', $value->getType());
    }

    public function testIsEqualWithSameValue(): void
    {
        $val1 = new UnsignedIntegerValue(42);
        $val2 = new UnsignedIntegerValue(42);

        static::assertTrue($val1->isEqual($val2));
    }

    public function testIsEqualWithDifferentValue(): void
    {
        $val1 = new UnsignedIntegerValue(42);
        $val2 = new UnsignedIntegerValue(43);

        static::assertFalse($val1->isEqual($val2));
    }

    public function testIsEqualWithIntegerValue(): void
    {
        $uint = new UnsignedIntegerValue(42);
        $int = new IntegerValue(42);

        static::assertTrue($uint->isEqual($int));
    }

    public function testIsEqualWithDifferentIntegerValue(): void
    {
        $uint = new UnsignedIntegerValue(42);
        $int = new IntegerValue(43);

        static::assertFalse($uint->isEqual($int));
    }

    public function testIsLessThan(): void
    {
        $val1 = new UnsignedIntegerValue(10);
        $val2 = new UnsignedIntegerValue(20);

        static::assertTrue($val1->isLessThan($val2));
        static::assertFalse($val2->isLessThan($val1));
    }

    public function testIsLessThanWithNonUnsignedIntegerThrowsException(): void
    {
        $uint = new UnsignedIntegerValue(42);
        $int = new IntegerValue(42);

        $this->expectException(UnsupportedOperationException::class);
        $uint->isLessThan($int);
    }

    public function testIsGreaterThan(): void
    {
        $val1 = new UnsignedIntegerValue(20);
        $val2 = new UnsignedIntegerValue(10);

        static::assertTrue($val1->isGreaterThan($val2));
        static::assertFalse($val2->isGreaterThan($val1));
    }

    public function testIsGreaterThanWithNonUnsignedIntegerThrowsException(): void
    {
        $uint = new UnsignedIntegerValue(42);
        $int = new IntegerValue(42);

        $this->expectException(UnsupportedOperationException::class);
        $uint->isGreaterThan($int);
    }
}
