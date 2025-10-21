<?php

declare(strict_types=1);

namespace Cel\Tests\Value;

use Cel\Exception\UnsupportedOperationException;
use Cel\Value\IntegerValue;
use Cel\Value\ListValue;
use Cel\Value\ValueKind;
use PHPUnit\Framework\TestCase;

final class ListValueTest extends TestCase
{
    public function testgetRawValue(): void
    {
        $value = new ListValue([new IntegerValue(1), new IntegerValue(2)]);

        $native = $value->getRawValue();

        static::assertSame([1, 2], $native);
    }

    public function testGetKind(): void
    {
        $value = new ListValue([]);

        static::assertSame(ValueKind::List, $value->getKind());
    }

    public function testGetType(): void
    {
        $value = new ListValue([]);

        static::assertSame('list', $value->getType());
    }

    public function testIsEqualWithSameList(): void
    {
        $val1 = new ListValue([new IntegerValue(1), new IntegerValue(2)]);
        $val2 = new ListValue([new IntegerValue(1), new IntegerValue(2)]);

        static::assertTrue($val1->isEqual($val2));
    }

    public function testIsEqualWithDifferentLength(): void
    {
        $val1 = new ListValue([new IntegerValue(1)]);
        $val2 = new ListValue([new IntegerValue(1), new IntegerValue(2)]);

        static::assertFalse($val1->isEqual($val2));
    }

    public function testIsEqualWithDifferentValues(): void
    {
        $val1 = new ListValue([new IntegerValue(1)]);
        $val2 = new ListValue([new IntegerValue(2)]);

        static::assertFalse($val1->isEqual($val2));
    }

    public function testIsEqualWithNonListThrowsException(): void
    {
        $list = new ListValue([]);
        $int = new IntegerValue(42);

        $this->expectException(UnsupportedOperationException::class);
        $list->isEqual($int);
    }

    public function testIsLessThanThrowsException(): void
    {
        $list1 = new ListValue([]);
        $list2 = new ListValue([]);

        $this->expectException(UnsupportedOperationException::class);
        $list1->isLessThan($list2);
    }

    public function testIsGreaterThanThrowsException(): void
    {
        $list1 = new ListValue([]);
        $list2 = new ListValue([]);

        $this->expectException(UnsupportedOperationException::class);
        $list1->isGreaterThan($list2);
    }
}
