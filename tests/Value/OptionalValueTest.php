<?php

declare(strict_types=1);

namespace Cel\Tests\Value;

use Cel\Exception\UnsupportedOperationException;
use Cel\Value\IntegerValue;
use Cel\Value\OptionalValue;
use Cel\Value\StringValue;
use Cel\Value\ValueKind;
use PHPUnit\Framework\TestCase;

final class OptionalValueTest extends TestCase
{
    public function testOfHoldsValue(): void
    {
        $inner = new IntegerValue(5);
        $optional = OptionalValue::of($inner);

        static::assertTrue($optional->hasValue());
        static::assertSame($inner, $optional->value);
        static::assertSame(ValueKind::Optional, $optional->getKind());
        static::assertSame('optional_type', $optional->getType());
    }

    public function testNoneIsEmpty(): void
    {
        $optional = OptionalValue::none();

        static::assertFalse($optional->hasValue());
        static::assertNull($optional->value);
    }

    public function testConstructorDefaultsToEmpty(): void
    {
        $optional = new OptionalValue();

        static::assertFalse($optional->hasValue());
        static::assertNull($optional->value);
    }

    public function testGetRawValueOfPresentReturnsInnerRawValue(): void
    {
        static::assertSame(5, OptionalValue::of(new IntegerValue(5))->getRawValue());
    }

    public function testGetRawValueOfEmptyReturnsNull(): void
    {
        static::assertNull(OptionalValue::none()->getRawValue());
    }

    public function testEmptyEqualsEmpty(): void
    {
        static::assertTrue(OptionalValue::none()->isEqual(OptionalValue::none()));
    }

    public function testPresentEqualsPresentWithEqualValue(): void
    {
        static::assertTrue(OptionalValue::of(new IntegerValue(1))->isEqual(OptionalValue::of(new IntegerValue(1))));
    }

    public function testPresentNotEqualPresentWithDifferentValue(): void
    {
        static::assertFalse(OptionalValue::of(new IntegerValue(1))->isEqual(OptionalValue::of(new IntegerValue(2))));
    }

    public function testEmptyNotEqualPresent(): void
    {
        static::assertFalse(OptionalValue::none()->isEqual(OptionalValue::of(new IntegerValue(1))));
    }

    public function testPresentNotEqualEmpty(): void
    {
        static::assertFalse(OptionalValue::of(new IntegerValue(1))->isEqual(OptionalValue::none()));
    }

    public function testNotEqualToNonOptional(): void
    {
        static::assertFalse(OptionalValue::of(new IntegerValue(1))->isEqual(new IntegerValue(1)));
        static::assertFalse(OptionalValue::none()->isEqual(new StringValue('x')));
    }

    public function testIsLessThanThrows(): void
    {
        $this->expectException(UnsupportedOperationException::class);

        OptionalValue::of(new IntegerValue(1))->isLessThan(OptionalValue::of(new IntegerValue(2)));
    }

    public function testIsGreaterThanThrows(): void
    {
        $this->expectException(UnsupportedOperationException::class);

        OptionalValue::of(new IntegerValue(1))->isGreaterThan(OptionalValue::of(new IntegerValue(2)));
    }
}
