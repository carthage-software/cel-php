<?php

declare(strict_types=1);

namespace Cel\Tests\Value;

use Cel\Exception\UnsupportedOperationException;
use Cel\Value\IntegerValue;
use Cel\Value\StringValue;
use Cel\Value\ValueKind;
use PHPUnit\Framework\TestCase;

final class StringValueTest extends TestCase
{
    public function testgetRawValue(): void
    {
        $value = new StringValue('hello');

        static::assertSame('hello', $value->getRawValue());
    }

    public function testGetKind(): void
    {
        $value = new StringValue('test');

        static::assertSame(ValueKind::String, $value->getKind());
    }

    public function testGetType(): void
    {
        $value = new StringValue('test');

        static::assertSame('string', $value->getType());
    }

    public function testIsEqualWithSameString(): void
    {
        $val1 = new StringValue('test');
        $val2 = new StringValue('test');

        static::assertTrue($val1->isEqual($val2));
    }

    public function testIsEqualWithDifferentString(): void
    {
        $val1 = new StringValue('test1');
        $val2 = new StringValue('test2');

        static::assertFalse($val1->isEqual($val2));
    }

    public function testIsEqualWithNonStringThrowsException(): void
    {
        $str = new StringValue('test');
        $int = new IntegerValue(42);

        $this->expectException(UnsupportedOperationException::class);
        $str->isEqual($int);
    }

    public function testIsLessThan(): void
    {
        $val1 = new StringValue('a');
        $val2 = new StringValue('b');

        static::assertTrue($val1->isLessThan($val2));
        static::assertFalse($val2->isLessThan($val1));
    }

    public function testIsLessThanWithNonStringThrowsException(): void
    {
        $str = new StringValue('test');
        $int = new IntegerValue(42);

        $this->expectException(UnsupportedOperationException::class);
        $str->isLessThan($int);
    }

    public function testIsGreaterThan(): void
    {
        $val1 = new StringValue('b');
        $val2 = new StringValue('a');

        static::assertTrue($val1->isGreaterThan($val2));
        static::assertFalse($val2->isGreaterThan($val1));
    }

    public function testIsGreaterThanWithNonStringThrowsException(): void
    {
        $str = new StringValue('test');
        $int = new IntegerValue(42);

        $this->expectException(UnsupportedOperationException::class);
        $str->isGreaterThan($int);
    }
}
