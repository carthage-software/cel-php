<?php

declare(strict_types=1);

namespace Cel\Tests\Runtime\Value;

use Cel\Runtime\Exception\UnsupportedOperationException;
use Cel\Runtime\Value\BooleanValue;
use Cel\Runtime\Value\DurationValue;
use Cel\Runtime\Value\ValueKind;
use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\TestCase;
use Psl\DateTime\Duration;

final class DurationValueTest extends TestCase
{
    public function testConstructorAndGetNativeValue(): void
    {
        $duration = Duration::days(1);
        $value = new DurationValue($duration);
        static::assertSame($duration, $value->getNativeValue());
    }

    public function testGetKindAndGetType(): void
    {
        $value = new DurationValue(Duration::days(1));
        static::assertSame(ValueKind::Duration, $value->getKind());
        static::assertSame('duration', $value->getType());
    }

    #[DataProvider('provideIsEqualCases')]
    public function testIsEqual(DurationValue $self, DurationValue $other, bool $expected): void
    {
        static::assertSame($expected, $self->isEqual($other));
    }

    public static function provideIsEqualCases(): iterable
    {
        yield 'equal durations' =>
            [
                new DurationValue(Duration::days(1)),
                new DurationValue(Duration::days(1)),
                true,
            ];
        yield 'not equal durations' =>
            [
                new DurationValue(Duration::days(1)),
                new DurationValue(Duration::days(2)),
                false,
            ];
        yield 'equal durations with different units' =>
            [
                new DurationValue(Duration::hours(24)),
                new DurationValue(Duration::days(1)),
                true,
            ];
    }

    public function testIsEqualThrowsExceptionForDifferentValueType(): void
    {
        $this->expectException(UnsupportedOperationException::class);
        $this->expectExceptionMessage('Cannot compare values of type `duration` and `bool` for equality');

        $durationValue = new DurationValue(Duration::days(1));
        $booleanValue = new BooleanValue(true);

        $durationValue->isEqual($booleanValue);
    }

    #[DataProvider('provideIsGreaterThanCases')]
    public function testIsGreaterThan(DurationValue $self, DurationValue $other, bool $expected): void
    {
        static::assertSame($expected, $self->isGreaterThan($other));
    }

    public static function provideIsGreaterThanCases(): iterable
    {
        yield 'greater than' =>
            [
                new DurationValue(Duration::days(2)),
                new DurationValue(Duration::days(1)),
                true,
            ];
        yield 'not greater than (equal)' =>
            [
                new DurationValue(Duration::days(1)),
                new DurationValue(Duration::days(1)),
                false,
            ];
        yield 'not greater than (less)' =>
            [
                new DurationValue(Duration::days(1)),
                new DurationValue(Duration::days(2)),
                false,
            ];
    }

    public function testIsGreaterThanThrowsExceptionForDifferentValueType(): void
    {
        $this->expectException(UnsupportedOperationException::class);
        $this->expectExceptionMessage('Cannot compare values of type `duration` and `bool`');

        $durationValue = new DurationValue(Duration::days(1));
        $booleanValue = new BooleanValue(true);

        $durationValue->isGreaterThan($booleanValue);
    }

    #[DataProvider('provideIsLessThanCases')]
    public function testIsLessThan(DurationValue $self, DurationValue $other, bool $expected): void
    {
        static::assertSame($expected, $self->isLessThan($other));
    }

    public static function provideIsLessThanCases(): iterable
    {
        yield 'less than' =>
            [
                new DurationValue(Duration::days(1)),
                new DurationValue(Duration::days(2)),
                true,
            ];
        yield 'not less than (equal)' =>
            [
                new DurationValue(Duration::days(1)),
                new DurationValue(Duration::days(1)),
                false,
            ];
        yield 'not less than (greater)' =>
            [
                new DurationValue(Duration::days(2)),
                new DurationValue(Duration::days(1)),
                false,
            ];
    }

    public function testIsLessThanThrowsExceptionForDifferentValueType(): void
    {
        $this->expectException(UnsupportedOperationException::class);
        $this->expectExceptionMessage('Cannot compare values of type `duration` and `bool`');

        $durationValue = new DurationValue(Duration::days(1));
        $booleanValue = new BooleanValue(true);

        $durationValue->isLessThan($booleanValue);
    }
}
