<?php

declare(strict_types=1);

namespace Cel\Tests\Runtime\Value;

use Cel\Exception\UnsupportedOperationException;
use Cel\Value\IntegerValue;
use Cel\Value\NullValue;
use Cel\Value\ValueKind;
use PHPUnit\Framework\TestCase;

final class NullValueTest extends TestCase
{
    public function testValue(): void
    {
        $value = new NullValue();
        static::assertNull($value->getRawValue());
        static::assertSame('null', $value->getType());
    }

    public function testGetKind(): void
    {
        $value = new NullValue();
        static::assertSame(ValueKind::Null, $value->getKind());
    }

    public function testIsEqualWithNullValue(): void
    {
        $null1 = new NullValue();
        $null2 = new NullValue();

        static::assertTrue($null1->isEqual($null2));
    }

    public function testIsEqualWithNonNullValueThrowsException(): void
    {
        $null = new NullValue();
        $int = new IntegerValue(42);

        $this->expectException(UnsupportedOperationException::class);
        $null->isEqual($int);
    }

    public function testIsLessThanThrowsException(): void
    {
        $null1 = new NullValue();
        $null2 = new NullValue();

        $this->expectException(UnsupportedOperationException::class);
        $null1->isLessThan($null2);
    }

    public function testIsGreaterThanThrowsException(): void
    {
        $null1 = new NullValue();
        $null2 = new NullValue();

        $this->expectException(UnsupportedOperationException::class);
        $null1->isGreaterThan($null2);
    }
}
