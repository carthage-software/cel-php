<?php

declare(strict_types=1);

namespace Cel\Tests\Runtime\Extension;

use Cel\Runtime\Exception\NoSuchOverloadException;
use Cel\Runtime\Exception\OverflowException;
use Cel\Runtime\Exception\RuntimeException;
use Cel\Runtime\Exception\TypeConversionException;
use Cel\Runtime\Extension\Core;
use Cel\Runtime\Value\BooleanValue;
use Cel\Runtime\Value\BytesValue;
use Cel\Runtime\Value\FloatValue;
use Cel\Runtime\Value\IntegerValue;
use Cel\Runtime\Value\StringValue;
use Cel\Runtime\Value\TimestampValue;
use Cel\Runtime\Value\UnsignedIntegerValue;
use Cel\Runtime\Value\Value;
use Cel\Span\Span;
use Cel\Tests\Runtime\RuntimeTestCase;
use Override;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\Attributes\Medium;
use Psl\DateTime\Timestamp;

/**
 * @mago-expect lint:halstead
 */
#[CoversClass(Core\CoreExtension::class)]
#[CoversClass(Core\Function\IntFunction::class)]
#[CoversClass(Core\Function\StringFunction::class)]
#[CoversClass(Core\Function\UIntFunction::class)]
#[CoversClass(Core\Function\FloatFunction::class)]
#[CoversClass(Core\Function\BoolFunction::class)]
#[CoversClass(Core\Function\SizeFunction::class)]
#[CoversClass(Core\Function\BytesFunction::class)]
#[CoversClass(Core\Function\TypeOfFunction::class)]
#[Medium]
final class CoreExtensionTest extends RuntimeTestCase
{
    /**
     * @return iterable<string, array{0: string, 1: array<string, mixed>, 2: Value|RuntimeException}>
     */
    #[Override]
    public static function provideEvaluationCases(): iterable
    {
        yield 'Core typeOf: boolean' => ['typeOf(true)', [], new StringValue('bool')];
        yield 'Core typeOf: integer' => ['typeOf(1)', [], new StringValue('int')];
        yield 'Core typeOf: float' => ['typeOf(1.0)', [], new StringValue('float')];
        yield 'Core typeOf: string' => ['typeOf("hello")', [], new StringValue('string')];
        yield 'Core typeOf: list' => ['typeOf([1, 2])', [], new StringValue('list')];
        yield 'Core typeOf: map' => ['typeOf({"a": 1})', [], new StringValue('map')];
        yield 'Core typeOf: null' => ['typeOf(null)', [], new StringValue('null')];
        yield 'Core typeOf: unsigned integer' => ['typeOf(1u)', [], new StringValue('uint')];

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

        yield 'Core float: boolean true' => ['float(true)', [], new FloatValue(1.0)];
        yield 'Core float: boolean false' => ['float(false)', [], new FloatValue(0.0)];
        yield 'Core float: integer' => ['float(123)', [], new FloatValue(123.0)];
        yield 'Core float: float' => ['float(1.23)', [], new FloatValue(1.23)];
        yield 'Core float: string integer' => ['float("123")', [], new FloatValue(123.0)];
        yield 'Core float: string float' => ['float("1.23")', [], new FloatValue(1.23)];
        yield 'Core float: unsigned integer' => ['float(1u)', [], new FloatValue(1.0)];

        yield 'Core bool: boolean true' => ['bool(true)', [], new BooleanValue(true)];
        yield 'Core bool: boolean false' => ['bool(false)', [], new BooleanValue(false)];
        yield 'Core bool: integer non-zero' => ['bool(1)', [], new BooleanValue(true)];
        yield 'Core bool: integer zero' => ['bool(0)', [], new BooleanValue(false)];
        yield 'Core bool: float non-zero' => ['bool(1.0)', [], new BooleanValue(true)];
        yield 'Core bool: float zero' => ['bool(0.0)', [], new BooleanValue(false)];
        yield 'Core bool: unsigned integer non-zero' => ['bool(1u)', [], new BooleanValue(true)];
        yield 'Core bool: unsigned integer zero' => ['bool(0u)', [], new BooleanValue(false)];

        yield 'Core bool: empty string error' =>
            [
                'bool("")',
                [],
                new TypeConversionException('Cannot convert string "" to boolean.', new Span(0, 8)),
            ];

        yield 'Core bool: bytes true' => ['bool(b"true")', [], new BooleanValue(true)];
        yield 'Core bool: bytes false' => ['bool(b"false")', [], new BooleanValue(false)];
        yield 'Core bool: empty bytes error' =>
            [
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
        yield 'Core size: unsupported type (int)' =>
            [
                'size(123)',
                [],
                new NoSuchOverloadException(
                    'Invalid arguments for function "size". Got `(int)`, but expected one of: `(string)`, `(bytes)`, `(list)`, or `(map)`',
                    new Span(0, 7),
                ),
            ];
        yield 'Core size: unsupported type (bool)' =>
            [
                'size(true)',
                [],
                new NoSuchOverloadException(
                    'Invalid arguments for function "size". Got `(bool)`, but expected one of: `(string)`, `(bytes)`, `(list)`, or `(map)`',
                    new Span(0, 8),
                ),
            ];

        yield 'Core bytes: from bytes' => ['bytes(b"hello")', [], new BytesValue('hello')];
        yield 'Core bytes: from string' => ['bytes("world")', [], new BytesValue('world')];

        yield 'Core float: from bytes' => ['float(b"123.45")', [], new FloatValue(123.45)];

        yield 'Core int: float overflow high' =>
            [
                'int(9.3e18)',
                [],
                new OverflowException(
                    'Float value 9.3E+18 overflows maximum integer value 9223372036854775807',
                    new Span(0, 11),
                ),
            ];

        yield 'Core int: float overflow low' =>
            [
                'int(-9.3e18)',
                [],
                new OverflowException(
                    'Float value -9.3E+18 overflows maximum integer value 9223372036854775807',
                    new Span(0, 12),
                ),
            ];

        yield 'Core int: from bytes' => ['int(b"123")', [], new IntegerValue(123)];

        yield 'Core string: from timestamp' =>
            [
                'string(t)',
                ['t' => new TimestampValue(Timestamp::fromParts(1757766605, 123000000))],
                new StringValue('2025-09-13T12:30:05.123Z'),
            ];

        yield 'Core string: from bytes' => ['string(b"hello")', [], new StringValue('hello')];

        yield 'Core string: from duration' => ['string(duration("1h"))', [], new StringValue('3600s')];

        yield 'Core uint: from int' => ['uint(123)', [], new UnsignedIntegerValue(123)];
        yield 'Core uint: from negative int' =>
            [
                'uint(-1)',
                [],
                new OverflowException('Integer value -1 overflows unsigned integer', new Span(0, 8)),
            ];

        yield 'Core uint: from zero int' => ['uint(0)', [], new UnsignedIntegerValue(0)];

        yield 'Core uint: from float' => ['uint(123.45)', [], new UnsignedIntegerValue(123)];
        yield 'Core uint: from negative float' =>
            [
                'uint(-1.23)',
                [],
                new OverflowException('Float value -1.230000 overflows unsigned integer', new Span(0, 11)),
            ];
        yield 'Core uint: from float infinity' =>
            [
                'uint(inf)',
                ['inf' => INF],
                new OverflowException('Float value INF overflows unsigned integer', new Span(0, 9)),
            ];

        yield 'Core uint: from zero float' => ['uint(0.0)', [], new UnsignedIntegerValue(0)];

        yield 'Core uint: from true' => ['uint(true)', [], new UnsignedIntegerValue(1)];
        yield 'Core uint: from false' => ['uint(false)', [], new UnsignedIntegerValue(0)];

        yield 'Core uint: from string' => ['uint("123")', [], new UnsignedIntegerValue(123)];
        yield 'Core uint: from invalid string' =>
            [
                'uint("abc")',
                [],
                new TypeConversionException('Cannot convert string "abc" to unsigned integer.', new Span(0, 11)),
            ];

        yield 'Core uint: from bytes' => ['uint(b"123")', [], new UnsignedIntegerValue(123)];
        yield 'Core uint: from invalid bytes' =>
            [
                'uint(b"abc")',
                [],
                new TypeConversionException('Cannot convert bytes "abc" to unsigned integer.', new Span(0, 12)),
            ];
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

    public function testFloatFunctionIsIdempotent(): void
    {
        $receipt = $this->evaluate('float(1.23)');

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

    public function testTypeOfFunctionIsIdempotent(): void
    {
        $receipt = $this->evaluate('typeOf(true)');

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
