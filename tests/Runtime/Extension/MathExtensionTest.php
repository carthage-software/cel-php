<?php

declare(strict_types=1);

namespace Cel\Tests\Runtime\Extension;

use Cel\Exception\EvaluationException;
use Cel\Span\Span;
use Cel\Tests\Runtime\RuntimeTestCase;
use Cel\Value\FloatValue;
use Cel\Value\IntegerValue;
use Cel\Value\StringValue;
use Cel\Value\Value;
use Override;

final class MathExtensionTest extends RuntimeTestCase
{
    /**
     * @return iterable<string, array{0: string, 1: array<string, mixed>, 2: Value|EvaluationException}>
     */
    #[Override]
    public static function provideEvaluationCases(): iterable
    {
        // baseConvert()
        yield 'Math baseConvert: hex to dec' => ['baseConvert("FF", 16, 10)', [], new StringValue('255')];
        yield 'Math baseConvert: dec to bin' => ['baseConvert("10", 10, 2)', [], new StringValue('1010')];
        yield 'Math baseConvert error: empty string' => [
            'baseConvert("", 16, 10)',
            [],
            new EvaluationException('baseConvert: cannot convert empty string', new Span(0, 22)),
        ];
        yield 'Math baseConvert error: from_base too low' => [
            'baseConvert("10", 1, 10)',
            [],
            new EvaluationException('baseConvert: from base 1 is not in the range 2-36', new Span(0, 23)),
        ];
        yield 'Math baseConvert error: to_base too high' => [
            'baseConvert("10", 10, 37)',
            [],
            new EvaluationException('baseConvert: to base 37 is not in the range 2-36', new Span(0, 24)),
        ];

        // clamp()
        yield 'Math clamp (int): value within range' => ['clamp(5, 0, 10)', [], new IntegerValue(5)];
        yield 'Math clamp (int): value below min' => ['clamp(-5, 0, 10)', [], new IntegerValue(0)];
        yield 'Math clamp (int): value above max' => ['clamp(15, 0, 10)', [], new IntegerValue(10)];
        yield 'Math clamp (float): value within range' => ['clamp(5.5, 0.0, 10.0)', [], new FloatValue(5.5)];
        yield 'Math clamp (float): value below min' => ['clamp(-5.5, 0.0, 10.0)', [], new FloatValue(0.0)];
        yield 'Math clamp (float): value above max' => ['clamp(15.5, 0.0, 10.0)', [], new FloatValue(10.0)];

        // fromBase()
        yield 'Math fromBase: hex to dec' => ['fromBase("FF", 16)', [], new IntegerValue(255)];
        yield 'Math fromBase: bin to dec' => ['fromBase("1010", 2)', [], new IntegerValue(10)];
        yield 'Math fromBase error: empty string' => [
            'fromBase("", 16)',
            [],
            new EvaluationException('fromBase: cannot convert empty string', new Span(0, 15)),
        ];
        yield 'Math fromBase error: base out of range' => [
            'fromBase("10", 37)',
            [],
            new EvaluationException('fromBase: base 37 is not in the range 2-36', new Span(0, 16)),
        ];

        // max()
        yield 'Math max: list of integers' => ['max([1, 5, 2])', [], new IntegerValue(5)];
        yield 'Math max: list of floats' => ['max([1.1, 5.5, 2.2])', [], new FloatValue(5.5)];
        yield 'Math max: list of mixed numbers' => ['max([1, 5.5, 2])', [], new FloatValue(5.5)];
        yield 'Math max error: empty list' => [
            'max([])',
            [],
            new EvaluationException('max() requires a non-empty list', new Span(0, 7)),
        ];
        yield 'Math max error: non-numeric type' => [
            'max([1, "a"])',
            [],
            new EvaluationException('max() only supports lists of integers and floats, got `string`', new Span(0, 12)),
        ];

        // mean()
        yield 'Math mean: list of integers' => ['mean([1, 2, 3])', [], new FloatValue(2.0)];
        yield 'Math mean: list of floats' => ['mean([1.0, 2.0, 4.0])', [], new FloatValue(2.333_333_333_333_333)];
        yield 'Math mean: list of mixed numbers' => ['mean([1, 2.0, 3])', [], new FloatValue(2.0)];
        yield 'Math mean error: empty list' => [
            'mean([])',
            [],
            new EvaluationException('mean() requires a non-empty list', new Span(0, 8)),
        ];
        yield 'Math mean error: non-numeric type' => [
            'mean([1, "a"])',
            [],
            new EvaluationException('mean() only supports lists of integers and floats, got `string`', new Span(0, 13)),
        ];

        // median()
        yield 'Math median: odd number of elements' => ['median([1, 3, 2, 5, 4])', [], new FloatValue(3.0)];
        yield 'Math median: even number of elements' => ['median([1, 4, 2, 3])', [], new FloatValue(2.5)];
        yield 'Math median: list of floats' => ['median([1.1, 3.3, 2.2])', [], new FloatValue(2.2)];
        yield 'Math median error: empty list' => [
            'median([])',
            [],
            new EvaluationException('median() requires a non-empty list', new Span(0, 10)),
        ];
        yield 'Math median error: non-numeric type' => [
            'median([1, "a"])',
            [],
            new EvaluationException(
                'median() only supports lists of integers and floats, got `string`',
                new Span(0, 15),
            ),
        ];

        // min()
        yield 'Math min: list of integers' => ['min([5, 1, 2])', [], new IntegerValue(1)];
        yield 'Math min: list of floats' => ['min([5.5, 1.1, 2.2])', [], new FloatValue(1.1)];
        yield 'Math min: list of mixed numbers' => ['min([5, 1.1, 2])', [], new FloatValue(1.1)];
        yield 'Math min error: empty list' => [
            'min([])',
            [],
            new EvaluationException('min() requires a non-empty list', new Span(0, 7)),
        ];
        yield 'Math min error: non-numeric type' => [
            'min([1, "a"])',
            [],
            new EvaluationException('min() only supports lists of integers and floats, got `string`', new Span(0, 12)),
        ];

        // sum()
        yield 'Math sum: list of integers' => ['sum([1, 2, 3])', [], new IntegerValue(6)];
        yield 'Math sum: empty list' => ['sum([])', [], new IntegerValue(0)];
        yield 'Math sum error: list of floats' => [
            'sum([1.0, 2.0])',
            [],
            new EvaluationException('sum() only supports lists of integers, got Cel\Value\FloatValue', new Span(0, 15)),
        ];
        yield 'Math sum error: list with non-integer' => [
            'sum([1, "a"])',
            [],
            new EvaluationException(
                'sum() only supports lists of integers, got Cel\Value\StringValue',
                new Span(0, 12),
            ),
        ];

        // toBase()
        yield 'Math toBase: dec to hex' => ['toBase(255, 16)', [], new StringValue('ff')];
        yield 'Math toBase: dec to bin' => ['toBase(10, 2)', [], new StringValue('1010')];
        yield 'Math toBase error: negative number' => [
            'toBase(-1, 10)',
            [],
            new EvaluationException(
                'toBase: number -1 is negative, only non-negative integers are supported',
                new Span(0, 14),
            ),
        ];
        yield 'Math toBase error: base out of range' => [
            'toBase(10, 37)',
            [],
            new EvaluationException('toBase: base 37 is not in the range 2-36', new Span(0, 14)),
        ];
    }

    public function testBaseConvertFunctionIsIdempotent(): void
    {
        $receipt = $this->evaluate('baseConvert("10", 10, 2)');
        static::assertTrue($receipt->idempotent);
    }

    public function testClampFunctionIsIdempotent(): void
    {
        $receipt = $this->evaluate('clamp(5, 0, 10)');
        static::assertTrue($receipt->idempotent);
    }

    public function testFromBaseFunctionIsIdempotent(): void
    {
        $receipt = $this->evaluate('fromBase("10", 2)');
        static::assertTrue($receipt->idempotent);
    }

    public function testMaxFunctionIsIdempotent(): void
    {
        $receipt = $this->evaluate('max([1, 2, 3])');
        static::assertTrue($receipt->idempotent);
    }

    public function testMeanFunctionIsIdempotent(): void
    {
        $receipt = $this->evaluate('mean([1, 2, 3])');
        static::assertTrue($receipt->idempotent);
    }

    public function testMedianFunctionIsIdempotent(): void
    {
        $receipt = $this->evaluate('median([1, 2, 3])');
        static::assertTrue($receipt->idempotent);
    }

    public function testMinFunctionIsIdempotent(): void
    {
        $receipt = $this->evaluate('min([1, 2, 3])');
        static::assertTrue($receipt->idempotent);
    }

    public function testSumFunctionIsIdempotent(): void
    {
        $receipt = $this->evaluate('sum([1, 2, 3])');
        static::assertTrue($receipt->idempotent);
    }

    public function testToBaseFunctionIsIdempotent(): void
    {
        $receipt = $this->evaluate('toBase(10, 2)');
        static::assertTrue($receipt->idempotent);
    }
}
