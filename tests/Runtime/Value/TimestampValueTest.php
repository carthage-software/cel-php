<?php

declare(strict_types=1);

namespace Cel\Tests\Runtime\Value;

use Cel\Runtime\Exception\UnsupportedOperationException;
use Cel\Runtime\Value\BooleanValue;
use Cel\Runtime\Value\TimestampValue;
use Cel\Runtime\Value\ValueKind;
use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\TestCase;
use Psl\DateTime\Timestamp;

final class TimestampValueTest extends TestCase
{
    public function testConstructorAndGetNativeValue(): void
    {
        $timestamp = Timestamp::fromParts(123456789);
        $value = new TimestampValue($timestamp);
        static::assertSame($timestamp, $value->getNativeValue());
    }

    public function testGetKindAndGetType(): void
    {
        $value = new TimestampValue(Timestamp::fromParts(123));
        static::assertSame(ValueKind::Timestamp, $value->getKind());
        static::assertSame('timestamp', $value->getType());
    }

    #[DataProvider('provideIsEqualCases')]
    public function testIsEqual(TimestampValue $self, TimestampValue $other, bool $expected): void
    {
        static::assertSame($expected, $self->isEqual($other));
    }

    public static function provideIsEqualCases(): iterable
    {
        yield 'equal timestamps' =>
            [
                new TimestampValue(Timestamp::fromParts(100)),
                new TimestampValue(Timestamp::fromParts(100)),
                true,
            ];
        yield 'not equal timestamps' =>
            [
                new TimestampValue(Timestamp::fromParts(100)),
                new TimestampValue(Timestamp::fromParts(200)),
                false,
            ];
    }

    public function testIsEqualThrowsExceptionForDifferentValueType(): void
    {
        $this->expectException(UnsupportedOperationException::class);
        $this->expectExceptionMessage('Cannot compare values of type `timestamp` and `bool` for equality');

        $timestampValue = new TimestampValue(Timestamp::fromParts(1));
        $booleanValue = new BooleanValue(true);

        $timestampValue->isEqual($booleanValue);
    }

    #[DataProvider('provideIsGreaterThanCases')]
    public function testIsGreaterThan(TimestampValue $self, TimestampValue $other, bool $expected): void
    {
        static::assertSame($expected, $self->isGreaterThan($other));
    }

    public static function provideIsGreaterThanCases(): iterable
    {
        yield 'greater than' =>
            [
                new TimestampValue(Timestamp::fromParts(200)),
                new TimestampValue(Timestamp::fromParts(100)),
                true,
            ];
        yield 'not greater than (equal)' =>
            [
                new TimestampValue(Timestamp::fromParts(100)),
                new TimestampValue(Timestamp::fromParts(100)),
                false,
            ];
        yield 'not greater than (less)' =>
            [
                new TimestampValue(Timestamp::fromParts(100)),
                new TimestampValue(Timestamp::fromParts(200)),
                false,
            ];
    }

    public function testIsGreaterThanThrowsExceptionForDifferentValueType(): void
    {
        $this->expectException(UnsupportedOperationException::class);
        $this->expectExceptionMessage('Cannot compare values of type `timestamp` and `bool`');

        $timestampValue = new TimestampValue(Timestamp::fromParts(1));
        $booleanValue = new BooleanValue(true);

        $timestampValue->isGreaterThan($booleanValue);
    }

    #[DataProvider('provideIsLessThanCases')]
    public function testIsLessThan(TimestampValue $self, TimestampValue $other, bool $expected): void
    {
        static::assertSame($expected, $self->isLessThan($other));
    }

    public static function provideIsLessThanCases(): iterable
    {
        yield 'less than' =>
            [
                new TimestampValue(Timestamp::fromParts(100)),
                new TimestampValue(Timestamp::fromParts(200)),
                true,
            ];
        yield 'not less than (equal)' =>
            [
                new TimestampValue(Timestamp::fromParts(100)),
                new TimestampValue(Timestamp::fromParts(100)),
                false,
            ];
        yield 'not less than (greater)' =>
            [
                new TimestampValue(Timestamp::fromParts(200)),
                new TimestampValue(Timestamp::fromParts(100)),
                false,
            ];
    }

    public function testIsLessThanThrowsExceptionForDifferentValueType(): void
    {
        $this->expectException(UnsupportedOperationException::class);
        $this->expectExceptionMessage('Cannot compare values of type `timestamp` and `bool`');

        $timestampValue = new TimestampValue(Timestamp::fromParts(1));
        $booleanValue = new BooleanValue(true);

        $timestampValue->isLessThan($booleanValue);
    }
}
