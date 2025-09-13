<?php

declare(strict_types=1);

namespace Cel\Tests\Runtime\Extension;

use Cel\Runtime\Exception\RuntimeException;
use Cel\Runtime\Extension\String as Strings;
use Cel\Runtime\Value\BooleanValue;
use Cel\Runtime\Value\IntegerValue;
use Cel\Runtime\Value\ListValue;
use Cel\Runtime\Value\StringValue;
use Cel\Runtime\Value\Value;
use Cel\Tests\Runtime\RuntimeTestCase;
use Override;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\Attributes\Medium;

#[CoversClass(Strings\StringExtension::class)]
#[CoversClass(Strings\Function\ContainsFunction::class)]
#[CoversClass(Strings\Function\EndsWithFunction::class)]
#[CoversClass(Strings\Function\IndexOfFunction::class)]
#[CoversClass(Strings\Function\LastIndexOfFunction::class)]
#[CoversClass(Strings\Function\ReplaceFunction::class)]
#[CoversClass(Strings\Function\SplitFunction::class)]
#[CoversClass(Strings\Function\StartsWithFunction::class)]
#[CoversClass(Strings\Function\ToAsciiLowerFunction::class)]
#[CoversClass(Strings\Function\ToAsciiUpperFunction::class)]
#[CoversClass(Strings\Function\ToLowerFunction::class)]
#[CoversClass(Strings\Function\ToUpperFunction::class)]
#[CoversClass(Strings\Function\TrimFunction::class)]
#[CoversClass(Strings\Function\TrimLeftFunction::class)]
#[CoversClass(Strings\Function\TrimRightFunction::class)]
#[Medium]
final class StringExtensionTest extends RuntimeTestCase
{
    /**
     * @return iterable<string, array{0: string, 1: array<string, mixed>, 2: Value|RuntimeException}>
     */
    #[Override]
    public static function provideEvaluationCases(): iterable
    {
        yield 'Strings contains: found' => ['contains("hello world", "world")', [], new BooleanValue(true)];
        yield 'Strings contains: not found' => ['contains("hello world", "galaxy")', [], new BooleanValue(false)];
        yield 'Strings contains: empty substring' => ['contains("hello", "")', [], new BooleanValue(true)];
        yield 'Strings contains: identical strings' => ['contains("hello", "hello")', [], new BooleanValue(true)];
        yield 'Strings contains: substring at start' =>
            [
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
        yield 'Strings indexOf: with offset not found' =>
            [
                'indexOf("hello mellow", "ello", 8)',
                [],
                new IntegerValue(-1),
            ];
        yield 'Strings indexOf: empty substring with offset' =>
            [
                'indexOf("hello mellow", "", 2)',
                [],
                new IntegerValue(2),
            ];
        yield 'Strings indexOf: negative offset error' =>
            [
                'indexOf("hello mellow", "ello", -1)',
                [],
                new IntegerValue(-1),
            ];

        yield 'Strings lastIndexOf: simple found' => ['lastIndexOf("hello mellow", "ello")', [], new IntegerValue(7)];
        yield 'Strings lastIndexOf: not found' => ['lastIndexOf("hello mellow", "jello")', [], new IntegerValue(-1)];
        yield 'Strings lastIndexOf: empty substring' => ['lastIndexOf("hello", "")', [], new IntegerValue(5)];
        yield 'Strings lastIndexOf: with offset' => ['lastIndexOf("hello mellow", "ello", 6)', [], new IntegerValue(7)];
        yield 'Strings lastIndexOf: with offset not found' =>
            [
                'lastIndexOf("hello mellow", "ello", 0)',
                [],
                new IntegerValue(7),
            ];
        yield 'Strings lastIndexOf: empty substring with offset' =>
            [
                'lastIndexOf("hello mellow", "", 0)',
                [],
                new IntegerValue(0),
            ];
        yield 'Strings lastIndexOf: negative offset error' =>
            [
                'lastIndexOf("hello mellow", "ello", -1)',
                [],
                new IntegerValue(-1),
            ];

        yield 'Strings replace: all occurrences' =>
            [
                'replace("hello hello", "he", "we")',
                [],
                new StringValue('wello wello'),
            ];
        yield 'Strings replace: empty needle' =>
            [
                'replace("hello hello", "", "_")',
                [],
                new StringValue('_h_e_l_l_o_ _h_e_l_l_o_'),
            ];
        yield 'Strings replace: empty replacement' =>
            [
                'replace("hello hello", "h", "")',
                [],
                new StringValue('ello ello'),
            ];

        yield 'Strings split: simple' =>
            [
                'split("a-b-c", "-")',
                [],
                new ListValue([new StringValue('a'), new StringValue('b'), new StringValue('c')]),
            ];
        yield 'Strings split: one limit' => ['split("a-b-c", "-", 1)', [], new ListValue([new StringValue('a-b-c')])];
        yield 'Strings split: two limit' =>
            [
                'split("a-b-c", "-", 2)',
                [],
                new ListValue([new StringValue('a'), new StringValue('b-c')]),
            ];
        yield 'Strings split: empty delimiter' =>
            [
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
        yield 'Strings toAsciiLower: with non-ascii' =>
            [
                'toAsciiLower("TacoCÆt Xii")',
                [],
                new StringValue('tacocÆt xii'),
            ];
        yield 'Strings toAsciiUpper: mixed case' => ['toAsciiUpper("TacoCat")', [], new StringValue('TACOCAT')];
        yield 'Strings toAsciiUpper: with non-ascii' =>
            [
                'toAsciiUpper("TacoCÆt Xii")',
                [],
                new StringValue('TACOCÆT XII'),
            ];
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
