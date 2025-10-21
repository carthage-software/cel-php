<?php

declare(strict_types=1);

namespace Cel\Tests\Runtime\Extension;

use Cel\Exception\EvaluationException;
use Cel\Tests\Runtime\RuntimeTestCase;
use Cel\Value\BooleanValue;
use Cel\Value\BytesValue;
use Cel\Value\IntegerValue;
use Cel\Value\ListValue;
use Cel\Value\StringValue;
use Cel\Value\Value;
use Override;

/**
 * @mago-expect lint:halstead
 */
final class StringExtensionTest extends RuntimeTestCase
{
    /**
     * @return iterable<string, array{0: string, 1: array<string, mixed>, 2: Value|EvaluationException}>
     */
    #[Override]
    public static function provideEvaluationCases(): iterable
    {
        yield 'Strings contains: found' => ['contains("hello world", "world")', [], new BooleanValue(true)];
        yield 'Strings contains: not found' => ['contains("hello world", "galaxy")', [], new BooleanValue(false)];
        yield 'Strings contains: empty substring' => ['contains("hello", "")', [], new BooleanValue(true)];
        yield 'Strings contains: identical strings' => ['contains("hello", "hello")', [], new BooleanValue(true)];
        yield 'Strings contains: substring at start' => [
            'contains("hello world", "hello")',
            [],
            new BooleanValue(true),
        ];
        yield 'Strings contains: substring at end' => ['contains("hello world", "world")', [], new BooleanValue(true)];

        yield 'Strings endsWith: found' => ['endsWith("hello world", "world")', [], new BooleanValue(true)];
        yield 'Strings endsWith: not found' => ['endsWith("hello world", "hello")', [], new BooleanValue(false)];
        yield 'Strings endsWith: empty substring' => ['endsWith("hello", "")', [], new BooleanValue(true)];
        yield 'Strings endsWith: identical strings' => ['endsWith("hello", "hello")', [], new BooleanValue(true)];

        yield 'Strings startsWith: found' => ['startsWith("hello world", "hello")', [], new BooleanValue(true)];
        yield 'Strings startsWith: not found' => ['startsWith("hello world", "world")', [], new BooleanValue(false)];
        yield 'Strings startsWith: empty substring' => ['startsWith("hello", "")', [], new BooleanValue(true)];
        yield 'Strings startsWith: identical strings' => ['startsWith("hello", "hello")', [], new BooleanValue(true)];

        yield 'Strings indexOf: simple found' => ['indexOf("hello mellow", "ello")', [], new IntegerValue(1)];
        yield 'Strings indexOf: not found' => ['indexOf("hello mellow", "jello")', [], new IntegerValue(-1)];
        yield 'Strings indexOf: empty substring' => ['indexOf("hello", "")', [], new IntegerValue(0)];
        yield 'Strings indexOf: with offset' => ['indexOf("hello mellow", "ello", 2)', [], new IntegerValue(7)];
        yield 'Strings indexOf: with offset not found' => [
            'indexOf("hello mellow", "ello", 8)',
            [],
            new IntegerValue(-1),
        ];
        yield 'Strings indexOf: empty substring with offset' => [
            'indexOf("hello mellow", "", 2)',
            [],
            new IntegerValue(2),
        ];
        yield 'Strings indexOf: negative offset error' => [
            'indexOf("hello mellow", "ello", -1)',
            [],
            new IntegerValue(-1),
        ];

        yield 'Strings lastIndexOf: simple found' => ['lastIndexOf("hello mellow", "ello")', [], new IntegerValue(7)];
        yield 'Strings lastIndexOf: not found' => ['lastIndexOf("hello mellow", "jello")', [], new IntegerValue(-1)];
        yield 'Strings lastIndexOf: empty substring' => ['lastIndexOf("hello", "")', [], new IntegerValue(5)];
        yield 'Strings lastIndexOf: with offset' => ['lastIndexOf("hello mellow", "ello", 6)', [], new IntegerValue(7)];
        yield 'Strings lastIndexOf: with offset not found' => [
            'lastIndexOf("hello mellow", "ello", 0)',
            [],
            new IntegerValue(7),
        ];
        yield 'Strings lastIndexOf: empty substring with offset' => [
            'lastIndexOf("hello mellow", "", 0)',
            [],
            new IntegerValue(0),
        ];
        yield 'Strings lastIndexOf: negative offset error' => [
            'lastIndexOf("hello mellow", "ello", -1)',
            [],
            new IntegerValue(-1),
        ];

        yield 'Strings replace: all occurrences' => [
            'replace("hello hello", "he", "we")',
            [],
            new StringValue('wello wello'),
        ];
        yield 'Strings replace: empty needle' => [
            'replace("hello hello", "", "_")',
            [],
            new StringValue('_h_e_l_l_o_ _h_e_l_l_o_'),
        ];
        yield 'Strings replace: empty replacement' => [
            'replace("hello hello", "h", "")',
            [],
            new StringValue('ello ello'),
        ];

        yield 'Strings split: simple' => [
            'split("a-b-c", "-")',
            [],
            new ListValue([new StringValue('a'), new StringValue('b'), new StringValue('c')]),
        ];
        yield 'Strings split: one limit' => ['split("a-b-c", "-", 1)', [], new ListValue([new StringValue('a-b-c')])];
        yield 'Strings split: two limit' => [
            'split("a-b-c", "-", 2)',
            [],
            new ListValue([new StringValue('a'), new StringValue('b-c')]),
        ];
        yield 'Strings split: empty delimiter' => [
            'split("hello", "")',
            [],
            new ListValue([
                new StringValue('h'),
                new StringValue('e'),
                new StringValue('l'),
                new StringValue('l'),
                new StringValue('o'),
            ]),
        ];

        yield 'Strings toAsciiLower: mixed case' => ['toAsciiLower("TacoCat")', [], new StringValue('tacocat')];
        yield 'Strings toAsciiLower: with non-ascii' => [
            'toAsciiLower("TacoCÆt Xii")',
            [],
            new StringValue('tacocÆt xii'),
        ];
        yield 'Strings toAsciiUpper: mixed case' => ['toAsciiUpper("TacoCat")', [], new StringValue('TACOCAT')];
        yield 'Strings toAsciiUpper: with non-ascii' => [
            'toAsciiUpper("TacoCÆt Xii")',
            [],
            new StringValue('TACOCÆT XII'),
        ];

        /// Bytes

        yield 'Bytes contains: found' => ['contains(b"hello world", b"world")', [], new BooleanValue(true)];
        yield 'Bytes contains: not found' => ['contains(b"hello world", b"galaxy")', [], new BooleanValue(false)];
        yield 'Bytes contains: empty substring' => ['contains(b"hello", b"")', [], new BooleanValue(true)];
        yield 'Bytes contains: identical strings' => ['contains(b"hello", b"hello")', [], new BooleanValue(true)];
        yield 'Bytes contains: substring at start' => [
            'contains(b"hello world", b"hello")',
            [],
            new BooleanValue(true),
        ];
        yield 'Bytes contains: substring at end' => ['contains(b"hello world", b"world")', [], new BooleanValue(true)];

        yield 'Bytes endsWith: found' => ['endsWith(b"hello world", b"world")', [], new BooleanValue(true)];
        yield 'Bytes endsWith: not found' => ['endsWith(b"hello world", b"hello")', [], new BooleanValue(false)];
        yield 'Bytes endsWith: empty substring' => ['endsWith(b"hello", b"")', [], new BooleanValue(true)];
        yield 'Bytes endsWith: identical strings' => ['endsWith(b"hello", b"hello")', [], new BooleanValue(true)];

        yield 'Bytes startsWith: found' => ['startsWith(b"hello world", b"hello")', [], new BooleanValue(true)];
        yield 'Bytes startsWith: not found' => ['startsWith(b"hello world", b"world")', [], new BooleanValue(false)];
        yield 'Bytes startsWith: empty substring' => ['startsWith(b"hello", b"")', [], new BooleanValue(true)];
        yield 'Bytes startsWith: identical strings' => ['startsWith(b"hello", b"hello")', [], new BooleanValue(true)];

        yield 'Bytes indexOf: simple found' => ['indexOf(b"hello mellow", b"ello")', [], new IntegerValue(1)];
        yield 'Bytes indexOf: not found' => ['indexOf(b"hello mellow", b"jello")', [], new IntegerValue(-1)];
        yield 'Bytes indexOf: empty substring' => ['indexOf(b"hello", b"")', [], new IntegerValue(0)];
        yield 'Bytes indexOf: with offset' => ['indexOf(b"hello mellow", b"ello", 2)', [], new IntegerValue(7)];
        yield 'Bytes indexOf: with offset not found' => [
            'indexOf(b"hello mellow", b"ello", 8)',
            [],
            new IntegerValue(-1),
        ];
        yield 'Bytes indexOf: empty substring with offset' => [
            'indexOf(b"hello mellow", b"", 2)',
            [],
            new IntegerValue(2),
        ];
        yield 'Bytes indexOf: negative offset error' => [
            'indexOf(b"hello mellow", b"ello", -1)',
            [],
            new IntegerValue(-1),
        ];

        yield 'Bytes lastIndexOf: simple found' => ['lastIndexOf(b"hello mellow", b"ello")', [], new IntegerValue(7)];
        yield 'Bytes lastIndexOf: not found' => ['lastIndexOf(b"hello mellow", b"jello")', [], new IntegerValue(-1)];
        yield 'Bytes lastIndexOf: empty substring' => ['lastIndexOf(b"hello", b"")', [], new IntegerValue(5)];
        yield 'Bytes lastIndexOf: with offset' => ['lastIndexOf(b"hello mellow", b"ello", 6)', [], new IntegerValue(7)];
        yield 'Bytes lastIndexOf: with offset not found' => [
            'lastIndexOf(b"hello mellow", b"ello", 0)',
            [],
            new IntegerValue(7),
        ];
        yield 'Bytes lastIndexOf: empty substring with offset' => [
            'lastIndexOf(b"hello mellow", b"", 0)',
            [],
            new IntegerValue(0),
        ];
        yield 'Bytes lastIndexOf: negative offset error' => [
            'lastIndexOf(b"hello mellow", b"ello", -1)',
            [],
            new IntegerValue(-1),
        ];

        yield 'Bytes replace: all occurrences' => [
            'replace(b"hello hello", b"he", b"we")',
            [],
            new BytesValue('wello wello'),
        ];
        yield 'Bytes replace: empty needle' => [
            'replace(b"hello hello", b"", b"_")',
            [],
            new BytesValue('_h_e_l_l_o_ _h_e_l_l_o_'),
        ];
        yield 'Bytes replace: empty replacement' => [
            'replace(b"hello hello", b"h", b"")',
            [],
            new BytesValue('ello ello'),
        ];

        yield 'Bytes split: simple' => [
            'split(b"a-b-c", b"-")',
            [],
            new ListValue([new BytesValue('a'), new BytesValue('b'), new BytesValue('c')]),
        ];
        yield 'Bytes split: one limit' => ['split(b"a-b-c", b"-", 1)', [], new ListValue([new BytesValue('a-b-c')])];
        yield 'Bytes split: two limit' => [
            'split(b"a-b-c", b"-", 2)',
            [],
            new ListValue([new BytesValue('a'), new BytesValue('b-c')]),
        ];
        yield 'Bytes split: empty delimiter' => [
            'split(b"hello", b"")',
            [],
            new ListValue([
                new BytesValue('h'),
                new BytesValue('e'),
                new BytesValue('l'),
                new BytesValue('l'),
                new BytesValue('o'),
            ]),
        ];

        yield 'Bytes toAsciiLower: mixed case' => ['toAsciiLower(b"TacoCat")', [], new BytesValue('tacocat')];
        yield 'Bytes toAsciiLower: with non-ascii' => [
            'toAsciiLower(b"TacoCÆt Xii")',
            [],
            new BytesValue('tacocÆt xii'),
        ];
        yield 'Bytes toAsciiUpper: mixed case' => ['toAsciiUpper(b"TacoCat")', [], new BytesValue('TACOCAT')];
        yield 'Bytes toAsciiUpper: with non-ascii' => [
            'toAsciiUpper(b"TacoCÆt Xii")',
            [],
            new BytesValue('TACOCÆT XII'),
        ];

        /// Trim functions

        yield 'Strings trim: simple' => ['trim("  foo  ")', [], new StringValue('foo')];
        yield 'Strings trim: with characters' => ['trim("__foo__", "_")', [], new StringValue('foo')];
        yield 'Strings trimLeft: simple' => ['trimLeft("  foo  ")', [], new StringValue('foo  ')];
        yield 'Strings trimLeft: with characters' => ['trimLeft("__foo__", "_")', [], new StringValue('foo__')];
        yield 'Strings trimRight: simple' => ['trimRight("  foo  ")', [], new StringValue('  foo')];
        yield 'Strings trimRight: with characters' => ['trimRight("__foo__", "_")', [], new StringValue('__foo')];

        yield 'Bytes trim: simple' => ['trim(b"  foo  ")', [], new BytesValue('foo')];
        yield 'Bytes trim: with characters' => ['trim(b"__foo__", b"_")', [], new BytesValue('foo')];
        yield 'Bytes trimLeft: simple' => ['trimLeft(b"  foo  ")', [], new BytesValue('foo  ')];
        yield 'Bytes trimLeft: with characters' => ['trimLeft(b"__foo__", b"_")', [], new BytesValue('foo__')];
        yield 'Bytes trimRight: simple' => ['trimRight(b"  foo  ")', [], new BytesValue('  foo')];
        yield 'Bytes trimRight: with characters' => ['trimRight(b"__foo__", b"_")', [], new BytesValue('__foo')];
    }

    public function testContainsFunctionIsIdempotent(): void
    {
        $receipt = $this->evaluate('contains("foo", "bar")');

        static::assertTrue($receipt->idempotent);
    }

    public function testEndsWithFunctionIsIdempotent(): void
    {
        $receipt = $this->evaluate('endsWith("foo", "bar")');

        static::assertTrue($receipt->idempotent);
    }

    public function testStartsWithFunctionIsIdempotent(): void
    {
        $receipt = $this->evaluate('startsWith("foo", "bar")');

        static::assertTrue($receipt->idempotent);
    }

    public function testIndexOfFunctionIsIdempotent(): void
    {
        $receipt = $this->evaluate('indexOf("foo", "o")');

        static::assertTrue($receipt->idempotent);
    }

    public function testLastIndexOfFunctionIsIdempotent(): void
    {
        $receipt = $this->evaluate('lastIndexOf("foo", "o")');

        static::assertTrue($receipt->idempotent);
    }

    public function testReplaceFunctionIsIdempotent(): void
    {
        $receipt = $this->evaluate('replace("foo", "o", "a")');

        static::assertTrue($receipt->idempotent);
    }

    public function testSplitFunctionIsIdempotent(): void
    {
        $receipt = $this->evaluate('split("foo-bar", "-")');

        static::assertTrue($receipt->idempotent);
    }

    public function testToAsciiLowerFunctionIsIdempotent(): void
    {
        $receipt = $this->evaluate('toAsciiLower("FOO")');

        static::assertTrue($receipt->idempotent);
    }

    public function testToAsciiUpperFunctionIsIdempotent(): void
    {
        $receipt = $this->evaluate('toAsciiUpper("foo")');

        static::assertTrue($receipt->idempotent);
    }

    public function testToLowerFunctionIsIdempotent(): void
    {
        $receipt = $this->evaluate('toLower("FOO")');

        static::assertTrue($receipt->idempotent);
    }

    public function testToUpperFunctionIsIdempotent(): void
    {
        $receipt = $this->evaluate('toUpper("foo")');

        static::assertTrue($receipt->idempotent);
    }

    public function testTrimFunctionIsIdempotent(): void
    {
        $receipt = $this->evaluate('trim("  foo  ")');

        static::assertTrue($receipt->idempotent);
    }

    public function testTrimLeftFunctionIsIdempotent(): void
    {
        $receipt = $this->evaluate('trimLeft("  foo  ")');

        static::assertTrue($receipt->idempotent);
    }

    public function testTrimRightFunctionIsIdempotent(): void
    {
        $receipt = $this->evaluate('trimRight("  foo  ")');

        static::assertTrue($receipt->idempotent);
    }
}
