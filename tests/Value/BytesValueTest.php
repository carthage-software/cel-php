<?php

declare(strict_types=1);

namespace Cel\Tests\Value;

use Cel\Exception\UnsupportedOperationException;
use Cel\Value\BytesValue;
use Cel\Value\IntegerValue;
use Cel\Value\ValueKind;
use PHPUnit\Framework\TestCase;

final class BytesValueTest extends TestCase
{
    public function testgetRawValue(): void
    {
        $value = new BytesValue('hello');

        static::assertSame('hello', $value->getRawValue());
    }

    public function testGetKind(): void
    {
        $value = new BytesValue('test');

        static::assertSame(ValueKind::Bytes, $value->getKind());
    }

    public function testGetType(): void
    {
        $value = new BytesValue('test');

        static::assertSame('bytes', $value->getType());
    }

    public function testIsEqualWithSameBytes(): void
    {
        $val1 = new BytesValue('test');
        $val2 = new BytesValue('test');

        static::assertTrue($val1->isEqual($val2));
    }

    public function testIsEqualWithDifferentBytes(): void
    {
        $val1 = new BytesValue('test1');
        $val2 = new BytesValue('test2');

        static::assertFalse($val1->isEqual($val2));
    }

    public function testIsEqualWithNonBytesThrowsException(): void
    {
        $bytes = new BytesValue('test');
        $int = new IntegerValue(42);

        $this->expectException(UnsupportedOperationException::class);
        $bytes->isEqual($int);
    }

    public function testIsLessThan(): void
    {
        $val1 = new BytesValue('a');
        $val2 = new BytesValue('b');

        static::assertTrue($val1->isLessThan($val2));
        static::assertFalse($val2->isLessThan($val1));
    }

    public function testIsLessThanWithNonBytesThrowsException(): void
    {
        $bytes = new BytesValue('test');
        $int = new IntegerValue(42);

        $this->expectException(UnsupportedOperationException::class);
        $bytes->isLessThan($int);
    }

    public function testIsGreaterThan(): void
    {
        $val1 = new BytesValue('b');
        $val2 = new BytesValue('a');

        static::assertTrue($val1->isGreaterThan($val2));
        static::assertFalse($val2->isGreaterThan($val1));
    }

    public function testIsGreaterThanWithNonBytesThrowsException(): void
    {
        $bytes = new BytesValue('test');
        $int = new IntegerValue(42);

        $this->expectException(UnsupportedOperationException::class);
        $bytes->isGreaterThan($int);
    }
}
