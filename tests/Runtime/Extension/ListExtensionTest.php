<?php

declare(strict_types=1);

namespace Cel\Tests\Runtime\Extension;

use Cel\Runtime\Exception\EvaluationException;
use Cel\Runtime\Value\BooleanValue;
use Cel\Runtime\Value\IntegerValue;
use Cel\Runtime\Value\ListValue;
use Cel\Runtime\Value\StringValue;
use Cel\Runtime\Value\Value;
use Cel\Span\Span;
use Cel\Tests\Runtime\RuntimeTestCase;
use Override;

final class ListExtensionTest extends RuntimeTestCase
{
    /**
     * @return iterable<string, array{0: string, 1: array<string, mixed>, 2: Value|EvaluationException}>
     */
    #[Override]
    public static function provideEvaluationCases(): iterable
    {
        yield 'Lists chunk: basic' => [
            'chunk([1, 2, 3, 4, 5], 2)',
            [],
            new ListValue([
                new ListValue([new IntegerValue(1), new IntegerValue(2)]),
                new ListValue([new IntegerValue(3), new IntegerValue(4)]),
                new ListValue([new IntegerValue(5)]),
            ]),
        ];
        yield 'Lists chunk: uneven' => [
            'chunk([1, 2, 3, 4, 5], 3)',
            [],
            new ListValue([
                new ListValue([new IntegerValue(1), new IntegerValue(2), new IntegerValue(3)]),
                new ListValue([new IntegerValue(4), new IntegerValue(5)]),
            ]),
        ];
        yield 'Lists chunk: empty list' => ['chunk([], 2)', [], new ListValue([])];
        yield 'Lists chunk: size 1' => [
            'chunk([1, 2, 3], 1)',
            [],
            new ListValue([
                new ListValue([new IntegerValue(1)]),
                new ListValue([new IntegerValue(2)]),
                new ListValue([new IntegerValue(3)]),
            ]),
        ];
        yield 'Lists chunk: zero size error' => [
            'chunk([1, 2, 3], 0)',
            [],
            new EvaluationException('Chunk size must be a positive integer', new Span(0, 15)),
        ];
        yield 'Lists chunk: negative size error' => [
            'chunk([1, 2, 3], -1)',
            [],
            new EvaluationException('Chunk size must be a positive integer', new Span(0, 16)),
        ];

        yield 'Lists contains: found' => ['contains([1, 2, 3], 2)', [], new BooleanValue(true)];
        yield 'Lists contains: not found' => ['contains([1, 2, 3], 4)', [], new BooleanValue(false)];
        yield 'Lists contains: empty list' => ['contains([], 1)', [], new BooleanValue(false)];
        yield 'Lists contains: mixed types found' => ['contains([1, "a", true], "a")', [], new BooleanValue(true)];
        yield 'Lists contains: mixed types not found' => ['contains([1, 2, 3], "2")', [], new BooleanValue(false)];

        yield 'Lists sort: integers' => [
            'sort([3, 1, 2])',
            [],
            new ListValue([new IntegerValue(1), new IntegerValue(2), new IntegerValue(3)]),
        ];
        yield 'Lists sort: strings' => [
            'sort(["c", "a", "b"])',
            [],
            new ListValue([new StringValue('a'), new StringValue('b'), new StringValue('c')]),
        ];
        yield 'Lists sort: empty list' => ['sort([])', [], new ListValue([])];
        yield 'Lists sort: already sorted' => [
            'sort([1, 2, 3])',
            [],
            new ListValue([new IntegerValue(1), new IntegerValue(2), new IntegerValue(3)]),
        ];
        yield 'Lists sort: mixed types error' => [
            'sort([1, "a"])',
            [],
            new EvaluationException('Cannot compare values of type `int` and `string`', new Span(0, 10)),
        ];

        yield 'Lists reverse: integers' => [
            'reverse([1, 2, 3])',
            [],
            new ListValue([new IntegerValue(3), new IntegerValue(2), new IntegerValue(1)]),
        ];
        yield 'Lists reverse: strings' => [
            'reverse(["a", "b", "c"])',
            [],
            new ListValue([new StringValue('c'), new StringValue('b'), new StringValue('a')]),
        ];
        yield 'Lists reverse: empty list' => ['reverse([])', [], new ListValue([])];
        yield 'Lists reverse: single element' => ['reverse([1])', [], new ListValue([new IntegerValue(1)])];

        yield 'Lists join: simple' => ['join(["a", "b", "c"])', [], new StringValue('abc')];
        yield 'Lists join: with separator' => ['join(["a", "b", "c"], "-")', [], new StringValue('a-b-c')];
        yield 'Lists join: empty list' => ['join([], "-")', [], new StringValue('')];
        yield 'Lists join: non-string list error' => [
            'join([1, 2, 3])',
            [],
            new EvaluationException('join: expects a list of strings', new Span(0, 17)),
        ];
    }

    public function testGetChunkFunctionIsIdempotent(): void
    {
        $receipt = $this->evaluate('[1, 2, 3, 4].chunk(2)');

        static::assertTrue($receipt->idempotent);
    }

    public function testGetContainsFunctionIsIdempotent(): void
    {
        $receipt = $this->evaluate('[1, 2, 3].contains(2)');

        static::assertTrue($receipt->idempotent);
    }

    public function testGetFlattenFunctionIsIdempotent(): void
    {
        $receipt = $this->evaluate('[[1, 2], [3, 4]].flatten()');

        static::assertTrue($receipt->idempotent);
    }

    public function testGetJoinFunctionIsIdempotent(): void
    {
        $receipt = $this->evaluate('["a", "b", "c"].join("-")');

        static::assertTrue($receipt->idempotent);
    }

    public function testGetReverseFunctionIsIdempotent(): void
    {
        $receipt = $this->evaluate('[1, 2, 3].reverse()');

        static::assertTrue($receipt->idempotent);
    }

    public function testGetSortFunctionIsIdempotent(): void
    {
        $receipt = $this->evaluate('[3, 1, 2].sort()');

        static::assertTrue($receipt->idempotent);
    }
}
