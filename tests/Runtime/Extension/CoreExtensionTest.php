<?php

declare(strict_types=1);

namespace Cel\Tests\Runtime\Extension;

use Cel\Runtime\Exception\NoSuchOverloadException;
use Cel\Runtime\Exception\RuntimeException;
use Cel\Runtime\Extension\Core;
use Cel\Runtime\Value\BooleanValue;
use Cel\Runtime\Value\FloatValue;
use Cel\Runtime\Value\IntegerValue;
use Cel\Runtime\Value\StringValue;
use Cel\Runtime\Value\Value;
use Cel\Span\Span;
use Cel\Tests\Runtime\RuntimeTestCase;
use Override;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\Attributes\Medium;

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
    }
}
