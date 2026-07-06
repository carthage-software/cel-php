<?php

declare(strict_types=1);

namespace Cel\Tests\Value;

use Cel\Value\BooleanValue;
use Cel\Value\BytesValue;
use Cel\Value\FloatValue;
use Cel\Value\IntegerValue;
use Cel\Value\NullValue;
use Cel\Value\StringValue;
use Cel\Value\UnsignedIntegerValue;
use Cel\Value\Value;
use Cel\Value\WellKnownType;
use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\TestCase;

final class WellKnownTypeTest extends TestCase
{
    #[DataProvider('provideConstructCases')]
    public function testConstruct(Value $expected, string $typename, Value $field): void
    {
        static::assertTrue($expected->isEqual(WellKnownType::construct($typename, [
            'value' => $field,
        ]) ?? new NullValue()));
    }

    /**
     * @return iterable<string, array{Value, string, Value}>
     */
    public static function provideConstructCases(): iterable
    {
        yield 'BoolValue unwraps its value' => [
            new BooleanValue(true),
            'google.protobuf.BoolValue',
            new BooleanValue(true),
        ];
        yield 'Int32Value unwraps its value' => [
            new IntegerValue(7),
            'google.protobuf.Int32Value',
            new IntegerValue(7),
        ];
        yield 'Int64Value unwraps its value' => [
            new IntegerValue(9),
            'google.protobuf.Int64Value',
            new IntegerValue(9),
        ];
        yield 'UInt32Value unwraps its value' => [
            new UnsignedIntegerValue(3),
            'google.protobuf.UInt32Value',
            new UnsignedIntegerValue(3),
        ];
        yield 'UInt64Value unwraps its value' => [
            new UnsignedIntegerValue(4),
            'google.protobuf.UInt64Value',
            new UnsignedIntegerValue(4),
        ];
        yield 'FloatValue unwraps its value' => [
            new FloatValue(1.5),
            'google.protobuf.FloatValue',
            new FloatValue(1.5),
        ];
        yield 'DoubleValue unwraps its value' => [
            new FloatValue(2.5),
            'google.protobuf.DoubleValue',
            new FloatValue(2.5),
        ];
        yield 'StringValue unwraps its value' => [
            new StringValue('x'),
            'google.protobuf.StringValue',
            new StringValue('x'),
        ];
        yield 'BytesValue unwraps its value' => [
            new BytesValue('x'),
            'google.protobuf.BytesValue',
            new BytesValue('x'),
        ];
    }

    #[DataProvider('provideEmptyCases')]
    public function testConstructEmptyYieldsZeroValue(Value $expected, string $typename): void
    {
        $result = WellKnownType::construct($typename, []);

        static::assertInstanceOf(Value::class, $result);
        static::assertTrue($expected->isEqual($result));
    }

    /**
     * @return iterable<string, array{Value, string}>
     */
    public static function provideEmptyCases(): iterable
    {
        yield 'BoolValue zero is false' => [new BooleanValue(false), 'google.protobuf.BoolValue'];
        yield 'Int32Value zero is 0' => [new IntegerValue(0), 'google.protobuf.Int32Value'];
        yield 'Int64Value zero is 0' => [new IntegerValue(0), 'google.protobuf.Int64Value'];
        yield 'UInt32Value zero is 0u' => [new UnsignedIntegerValue(0), 'google.protobuf.UInt32Value'];
        yield 'UInt64Value zero is 0u' => [new UnsignedIntegerValue(0), 'google.protobuf.UInt64Value'];
        yield 'FloatValue zero is 0.0' => [new FloatValue(0.0), 'google.protobuf.FloatValue'];
        yield 'DoubleValue zero is 0.0' => [new FloatValue(0.0), 'google.protobuf.DoubleValue'];
        yield 'StringValue zero is empty' => [new StringValue(''), 'google.protobuf.StringValue'];
        yield 'BytesValue zero is empty' => [new BytesValue(''), 'google.protobuf.BytesValue'];
        yield 'Value is null' => [new NullValue(), 'google.protobuf.Value'];
    }

    public function testUnsupportedWellKnownTypeReturnsNull(): void
    {
        static::assertNull(WellKnownType::construct('google.protobuf.Struct', []));
        static::assertNull(WellKnownType::construct('google.protobuf.Any', []));
    }

    public function testAllowedFieldsForWrappersIsValueOnly(): void
    {
        static::assertSame(['value'], WellKnownType::allowedFields('google.protobuf.Int64Value'));
        static::assertSame(['value'], WellKnownType::allowedFields('google.protobuf.StringValue'));
    }

    public function testAllowedFieldsForValueIsEmpty(): void
    {
        static::assertSame([], WellKnownType::allowedFields('google.protobuf.Value'));
    }

    public function testAllowedFieldsForUnsupportedTypeIsNull(): void
    {
        static::assertNull(WellKnownType::allowedFields('google.protobuf.Struct'));
        static::assertNull(WellKnownType::allowedFields('google.protobuf.Any'));
    }
}
