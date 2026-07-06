<?php

declare(strict_types=1);

namespace Cel\Tests\Runtime\Extension;

use Cel\Exception\EvaluationException;
use Cel\Exception\MessageConstructionException;
use Cel\Exception\NoSuchOverloadException;
use Cel\Exception\OverflowException;
use Cel\Exception\TypeConversionException;
use Cel\Span\Span;
use Cel\Tests\Runtime\RuntimeTestCase;
use Cel\Value\BooleanValue;
use Cel\Value\BytesValue;
use Cel\Value\DurationValue;
use Cel\Value\FloatValue;
use Cel\Value\IntegerValue;
use Cel\Value\StringValue;
use Cel\Value\TimestampValue;
use Cel\Value\TypeValue;
use Cel\Value\UnsignedIntegerValue;
use Cel\Value\Value;
use Override;
use Psl\DateTime\Duration;
use Psl\DateTime\Timestamp;

use const INF;

/**
 * @mago-expect lint:halstead
 */
final class CoreExtensionTest extends RuntimeTestCase
{
    /**
     * @return iterable<string, array{0: string, 1: array<string, mixed>, 2: Value|EvaluationException}>
     */
    #[Override]
    public static function provideEvaluationCases(): iterable
    {
        yield 'Core type: boolean' => ['type(true)', [], new TypeValue('bool')];
        yield 'Core type: integer' => ['type(1)', [], new TypeValue('int')];
        yield 'Core type: double' => ['type(1.0)', [], new TypeValue('double')];
        yield 'Core type: string' => ['type("hello")', [], new TypeValue('string')];
        yield 'Core type: bytes' => ['type(b"x")', [], new TypeValue('bytes')];
        yield 'Core type: list' => ['type([1, 2])', [], new TypeValue('list')];
        yield 'Core type: map' => ['type({"a": 1})', [], new TypeValue('map')];
        yield 'Core type: null' => ['type(null)', [], new TypeValue('null_type')];
        yield 'Core type: unsigned integer' => ['type(1u)', [], new TypeValue('uint')];
        yield 'Core type: type of a type is type' => ['type(type(1))', [], new TypeValue('type')];
        yield 'Core type: type of a denotation is type' => ['type(int)', [], new TypeValue('type')];
        yield 'Core type: duration' => ['type(duration("1s"))', [], new TypeValue('google.protobuf.Duration')];
        yield 'Core type: timestamp' => [
            'type(timestamp(0))',
            [],
            new TypeValue('google.protobuf.Timestamp'),
        ];

        yield 'Core type denotation: int' => ['int', [], new TypeValue('int')];
        yield 'Core type denotation: uint' => ['uint', [], new TypeValue('uint')];
        yield 'Core type denotation: double' => ['double', [], new TypeValue('double')];
        yield 'Core type denotation: bool' => ['bool', [], new TypeValue('bool')];
        yield 'Core type denotation: string' => ['string', [], new TypeValue('string')];
        yield 'Core type denotation: bytes' => ['bytes', [], new TypeValue('bytes')];
        yield 'Core type denotation: list' => ['list', [], new TypeValue('list')];
        yield 'Core type denotation: map' => ['map', [], new TypeValue('map')];
        yield 'Core type denotation: null_type' => ['null_type', [], new TypeValue('null_type')];
        yield 'Core type denotation: type' => ['type', [], new TypeValue('type')];
        yield 'Core type: equal types' => ['type(1) == type(2)', [], new BooleanValue(true)];
        yield 'Core type: unequal types' => ['type(1) == type(1u)', [], new BooleanValue(false)];
        yield 'Core type: denotation matches value type' => ['type(1) == int', [], new BooleanValue(true)];

        yield 'Core type denotation: qualified timestamp' => [
            'google.protobuf.Timestamp',
            [],
            new TypeValue('google.protobuf.Timestamp'),
        ];
        yield 'Core type denotation: qualified duration' => [
            'google.protobuf.Duration',
            [],
            new TypeValue('google.protobuf.Duration'),
        ];
        yield 'Core type: qualified timestamp denotation matches value type' => [
            'google.protobuf.Timestamp == type(timestamp(0))',
            [],
            new BooleanValue(true),
        ];
        yield 'Core type: qualified duration denotation matches value type' => [
            'google.protobuf.Duration == type(duration("1000000s"))',
            [],
            new BooleanValue(true),
        ];

        yield from self::provideWellKnownTypeCases();

        yield 'Core dyn: integer' => ['dyn(1)', [], new IntegerValue(1)];
        yield 'Core dyn: string' => ['dyn("hello")', [], new StringValue('hello')];
        yield 'Core dyn: list identity' => ['dyn([1, 2]) == [1, 2]', [], new BooleanValue(true)];
        yield 'Core dyn: preserves value' => ['dyn(3) + 4', [], new IntegerValue(7)];

        yield 'Core string: boolean' => ['string(true)', [], new StringValue('true')];
        yield 'Core string: integer' => ['string(123)', [], new StringValue('123')];
        yield 'Core string: float' => ['string(1.23)', [], new StringValue('1.23')];
        yield 'Core string: string' => ['string("hello")', [], new StringValue('hello')];
        yield 'Core string: unsigned integer' => ['string(1u)', [], new StringValue('1')];

        yield 'Core int: boolean true' => ['int(true)', [], new IntegerValue(1)];
        yield 'Core int: boolean false' => ['int(false)', [], new IntegerValue(0)];
        yield 'Core int: integer' => ['int(123)', [], new IntegerValue(123)];
        yield 'Core int: float positive' => ['int(1.23)', [], new IntegerValue(1)];
        yield 'Core int: float negative' => ['int(-1.23)', [], new IntegerValue(-1)];
        yield 'Core int: string integer' => ['int("123")', [], new IntegerValue(123)];
        yield 'Core int: unsigned integer' => ['int(1u)', [], new IntegerValue(1)];

        yield 'Core double: boolean true' => ['double(true)', [], new FloatValue(1.0)];
        yield 'Core double: boolean false' => ['double(false)', [], new FloatValue(0.0)];
        yield 'Core double: integer' => ['double(123)', [], new FloatValue(123.0)];
        yield 'Core double: double' => ['double(1.23)', [], new FloatValue(1.23)];
        yield 'Core double: string integer' => ['double("123")', [], new FloatValue(123.0)];
        yield 'Core double: string double' => ['double("1.23")', [], new FloatValue(1.23)];
        yield 'Core double: unsigned integer' => ['double(1u)', [], new FloatValue(1.0)];
        yield 'Core double: unsigned integer maximum keeps magnitude' => [
            'double(18446744073709551615u)',
            [],
            new FloatValue(18_446_744_073_709_551_615.0),
        ];

        yield 'Core bool: boolean true' => ['bool(true)', [], new BooleanValue(true)];
        yield 'Core bool: boolean false' => ['bool(false)', [], new BooleanValue(false)];
        yield 'Core bool: integer non-zero' => ['bool(1)', [], new BooleanValue(true)];
        yield 'Core bool: integer zero' => ['bool(0)', [], new BooleanValue(false)];
        yield 'Core bool: float non-zero' => ['bool(1.0)', [], new BooleanValue(true)];
        yield 'Core bool: float zero' => ['bool(0.0)', [], new BooleanValue(false)];
        yield 'Core bool: unsigned integer non-zero' => ['bool(1u)', [], new BooleanValue(true)];
        yield 'Core bool: unsigned integer zero' => ['bool(0u)', [], new BooleanValue(false)];

        yield 'Core bool: string 1' => ['bool("1")', [], new BooleanValue(true)];
        yield 'Core bool: string t' => ['bool("t")', [], new BooleanValue(true)];
        yield 'Core bool: string true' => ['bool("true")', [], new BooleanValue(true)];
        yield 'Core bool: string TRUE' => ['bool("TRUE")', [], new BooleanValue(true)];
        yield 'Core bool: string True' => ['bool("True")', [], new BooleanValue(true)];
        yield 'Core bool: string 0' => ['bool("0")', [], new BooleanValue(false)];
        yield 'Core bool: string f' => ['bool("f")', [], new BooleanValue(false)];
        yield 'Core bool: string false' => ['bool("false")', [], new BooleanValue(false)];
        yield 'Core bool: string FALSE' => ['bool("FALSE")', [], new BooleanValue(false)];
        yield 'Core bool: string False' => ['bool("False")', [], new BooleanValue(false)];
        yield 'Core bool: empty string error' => [
            'bool("")',
            [],
            new TypeConversionException('Cannot convert string "" to boolean.', new Span(0, 8)),
        ];
        yield 'Core bool: mixed-case string error' => [
            'bool("TrUe")',
            [],
            new TypeConversionException('Cannot convert string "TrUe" to boolean.', new Span(0, 12)),
        ];

        yield 'Core bool: bytes true' => ['bool(b"true")', [], new BooleanValue(true)];
        yield 'Core bool: bytes false' => ['bool(b"false")', [], new BooleanValue(false)];
        yield 'Core bool: empty bytes error' => [
            'bool(b"")',
            [],
            new TypeConversionException('Cannot convert bytes "" to boolean.', new Span(0, 9)),
        ];

        yield 'Core size: string' => ['size("hello")', [], new IntegerValue(5)];
        yield 'Core size: empty string' => ['size("")', [], new IntegerValue(0)];
        yield 'Core size: multibyte string' => ['size("你好")', [], new IntegerValue(2)];
        yield 'Core size: list' => ['size([1, 2, 3])', [], new IntegerValue(3)];
        yield 'Core size: empty list' => ['size([])', [], new IntegerValue(0)];
        yield 'Core size: map' => ['size({"a": 1, "b": 2})', [], new IntegerValue(2)];
        yield 'Core size: empty map' => ['size({})', [], new IntegerValue(0)];
        yield 'Core size: unsupported type (int)' => [
            'size(123)',
            [],
            new NoSuchOverloadException(
                'Invalid arguments for function "size". Got `(int)`, but expected one of: `(string)`, `(bytes)`, `(list)`, or `(map)`',
                new Span(0, 7),
            ),
        ];
        yield 'Core size: unsupported type (bool)' => [
            'size(true)',
            [],
            new NoSuchOverloadException(
                'Invalid arguments for function "size". Got `(bool)`, but expected one of: `(string)`, `(bytes)`, `(list)`, or `(map)`',
                new Span(0, 8),
            ),
        ];

        yield 'Core bytes: from bytes' => ['bytes(b"hello")', [], new BytesValue('hello')];
        yield 'Core bytes: from string' => ['bytes("world")', [], new BytesValue('world')];

        yield 'Core double: from bytes' => ['double(b"123.45")', [], new FloatValue(123.45)];

        yield 'Core int: float overflow high' => [
            'int(9.3e18)',
            [],
            new OverflowException('Double value 9.3E+18 overflows the integer range', new Span(0, 11)),
        ];

        yield 'Core int: float overflow low' => [
            'int(-9.3e18)',
            [],
            new OverflowException('Double value -9.3E+18 overflows the integer range', new Span(0, 12)),
        ];

        yield 'Core int: double at the positive boundary overflows' => [
            'int(9223372036854775807.0)',
            [],
            new OverflowException('Double value 9.2233720368548E+18 overflows the integer range', new Span(0, 26)),
        ];

        yield 'Core int: double at the negative boundary overflows' => [
            'int(-9223372036854775808.0)',
            [],
            new OverflowException('Double value -9.2233720368548E+18 overflows the integer range', new Span(0, 27)),
        ];

        yield 'Core int: NaN cannot convert to an integer' => [
            'int(0.0 / 0.0)',
            [],
            new OverflowException('Double value NaN or infinity overflows the integer range', new Span(0, 14)),
        ];

        yield 'Core int: from bytes' => ['int(b"123")', [], new IntegerValue(123)];

        yield 'Core int: from timestamp' => [
            'int(timestamp("2004-09-16T23:59:59Z"))',
            [],
            new IntegerValue(1_095_379_199),
        ];

        yield 'Core duration: identity' => [
            'duration(duration("100s"))',
            [],
            new DurationValue(Duration::fromParts(0, 0, 100)),
        ];

        yield 'Core timestamp: identity' => [
            'timestamp(timestamp(1000000000))',
            [],
            new TimestampValue(Timestamp::fromParts(1_000_000_000)),
        ];

        yield 'Core string: from timestamp' => [
            'string(t)',
            ['t' => new TimestampValue(Timestamp::fromParts(1_757_766_605, 123_000_000))],
            new StringValue('2025-09-13T12:30:05.123Z'),
        ];
        yield 'Core string: from timestamp without fraction' => [
            'string(timestamp("2009-02-13T23:31:30Z"))',
            [],
            new StringValue('2009-02-13T23:31:30Z'),
        ];
        yield 'Core string: from timestamp trims trailing zeros' => [
            'string(t)',
            ['t' => new TimestampValue(Timestamp::fromParts(1_757_766_605, 100_000_000))],
            new StringValue('2025-09-13T12:30:05.1Z'),
        ];
        yield 'Core string: from timestamp at the nanosecond boundary' => [
            'string(timestamp("9999-12-31T23:59:59.999999999Z"))',
            [],
            new StringValue('9999-12-31T23:59:59.999999999Z'),
        ];

        yield 'Core string: from bytes' => ['string(b"hello")', [], new StringValue('hello')];

        yield 'Core string: from duration' => ['string(duration("1h"))', [], new StringValue('3600s')];

        yield 'Core uint: from int' => ['uint(123)', [], new UnsignedIntegerValue(123)];
        yield 'Core uint: from negative int' => [
            'uint(-1)',
            [],
            new OverflowException('Integer value -1 overflows unsigned integer', new Span(0, 8)),
        ];

        yield 'Core uint: from zero int' => ['uint(0)', [], new UnsignedIntegerValue(0)];

        yield 'Core uint: from float' => ['uint(123.45)', [], new UnsignedIntegerValue(123)];
        yield 'Core uint: from negative float' => [
            'uint(-1.23)',
            [],
            new OverflowException('Float value -1.230000 overflows unsigned integer', new Span(0, 11)),
        ];
        yield 'Core uint: from float infinity' => [
            'uint(inf)',
            ['inf' => INF],
            new OverflowException('Float value INF overflows unsigned integer', new Span(0, 9)),
        ];

        yield 'Core uint: from zero float' => ['uint(0.0)', [], new UnsignedIntegerValue(0)];

        yield 'Core uint: from true' => ['uint(true)', [], new UnsignedIntegerValue(1)];
        yield 'Core uint: from false' => ['uint(false)', [], new UnsignedIntegerValue(0)];

        yield 'Core uint: from string' => ['uint("123")', [], new UnsignedIntegerValue(123)];
        yield 'Core uint: from invalid string' => [
            'uint("abc")',
            [],
            new TypeConversionException('Cannot convert string "abc" to unsigned integer.', new Span(0, 11)),
        ];

        yield 'Core uint: from bytes' => ['uint(b"123")', [], new UnsignedIntegerValue(123)];
        yield 'Core uint: from invalid bytes' => [
            'uint(b"abc")',
            [],
            new TypeConversionException('Cannot convert bytes "abc" to unsigned integer.', new Span(0, 12)),
        ];
    }

    /**
     * The `google.protobuf` scalar wrapper types unwrap to their underlying
     * primitive when constructed, an empty literal yields the primitive's zero
     * value, a constructed wrapper is never null, and `Value` yields null.
     *
     * @return iterable<string, array{0: string, 1: array<string, mixed>, 2: BooleanValue|MessageConstructionException}>
     */
    private static function provideWellKnownTypeCases(): iterable
    {
        yield 'WKT wrapper: unknown field is rejected' => [
            'google.protobuf.BoolValue{value: false, x: false}',
            [],
            new MessageConstructionException(
                'Field `x` is not defined on message type `google.protobuf.BoolValue`.',
                new Span(0, 0),
            ),
        ];
        yield 'WKT wrapper: sole unknown field is rejected' => [
            'google.protobuf.Int64Value{other: 1}',
            [],
            new MessageConstructionException(
                'Field `other` is not defined on message type `google.protobuf.Int64Value`.',
                new Span(0, 0),
            ),
        ];
        yield 'WKT Value: any field is rejected' => [
            'google.protobuf.Value{number_value: 1.0}',
            [],
            new MessageConstructionException(
                'Field `number_value` is not defined on message type `google.protobuf.Value`.',
                new Span(0, 0),
            ),
        ];

        yield 'WKT BoolValue: set' => ['google.protobuf.BoolValue{value: true} == true', [], new BooleanValue(true)];
        yield 'WKT BoolValue: empty' => ['google.protobuf.BoolValue{} == false', [], new BooleanValue(true)];
        yield 'WKT BoolValue: not null' => ['google.protobuf.BoolValue{} != null', [], new BooleanValue(true)];
        yield 'WKT BytesValue: set' => [
            "google.protobuf.BytesValue{value: b'set'} == b'set'",
            [],
            new BooleanValue(true),
        ];
        yield 'WKT BytesValue: empty' => ["google.protobuf.BytesValue{} == b''", [], new BooleanValue(true)];
        yield 'WKT DoubleValue: set' => [
            'google.protobuf.DoubleValue{value: -1.5} == -1.5',
            [],
            new BooleanValue(true),
        ];
        yield 'WKT DoubleValue: empty' => ['google.protobuf.DoubleValue{} == 0.0', [], new BooleanValue(true)];
        yield 'WKT FloatValue: set' => ['google.protobuf.FloatValue{value: -1.5} == -1.5', [], new BooleanValue(true)];
        yield 'WKT FloatValue: empty' => ['google.protobuf.FloatValue{} == 0.0', [], new BooleanValue(true)];
        yield 'WKT Int32Value: set' => ['google.protobuf.Int32Value{value: 123} == 123', [], new BooleanValue(true)];
        yield 'WKT Int32Value: empty' => ['google.protobuf.Int32Value{} == 0', [], new BooleanValue(true)];
        yield 'WKT Int64Value: set' => [
            'google.protobuf.Int64Value{value: 2147483650} == 2147483650',
            [],
            new BooleanValue(true),
        ];
        yield 'WKT Int64Value: not null' => ['google.protobuf.Int64Value{} != null', [], new BooleanValue(true)];
        yield 'WKT StringValue: set' => [
            "google.protobuf.StringValue{value: 'set'} == 'set'",
            [],
            new BooleanValue(true),
        ];
        yield 'WKT StringValue: empty' => ["google.protobuf.StringValue{} == ''", [], new BooleanValue(true)];
        yield 'WKT UInt32Value: set' => ['google.protobuf.UInt32Value{value: 42u} == 42u', [], new BooleanValue(true)];
        yield 'WKT UInt64Value: set' => [
            'google.protobuf.UInt64Value{value: 4294967296u} == 4294967296u',
            [],
            new BooleanValue(true),
        ];
        yield 'WKT UInt64Value: empty' => ['google.protobuf.UInt64Value{} == 0u', [], new BooleanValue(true)];
        yield 'WKT Value: empty is null' => ['dyn(google.protobuf.Value{}) == null', [], new BooleanValue(true)];
    }

    public function testBoolFunctionIsIdempotent(): void
    {
        $receipt = $this->evaluate('bool(true)');

        static::assertTrue($receipt->idempotent);
    }

    public function testIntFunctionIsIdempotent(): void
    {
        $receipt = $this->evaluate('int(123)');

        static::assertTrue($receipt->idempotent);
    }

    public function testDoubleFunctionIsIdempotent(): void
    {
        $receipt = $this->evaluate('double(1.23)');

        static::assertTrue($receipt->idempotent);
    }

    public function testDynFunctionIsIdempotent(): void
    {
        $receipt = $this->evaluate('dyn(1)');

        static::assertTrue($receipt->idempotent);
    }

    public function testStringFunctionIsIdempotent(): void
    {
        $receipt = $this->evaluate('string("hello")');

        static::assertTrue($receipt->idempotent);
    }

    public function testSizeFunctionIsIdempotent(): void
    {
        $receipt = $this->evaluate('size("hello")');

        static::assertTrue($receipt->idempotent);
    }

    public function testTypeFunctionIsIdempotent(): void
    {
        $receipt = $this->evaluate('type(true)');

        static::assertTrue($receipt->idempotent);
    }

    public function testUIntFunctionIsIdempotent(): void
    {
        $receipt = $this->evaluate('uint(123u)');

        static::assertTrue($receipt->idempotent);
    }

    public function testBytesFunctionIsIdempotent(): void
    {
        $receipt = $this->evaluate('bytes("hello")');

        static::assertTrue($receipt->idempotent);
    }
}
