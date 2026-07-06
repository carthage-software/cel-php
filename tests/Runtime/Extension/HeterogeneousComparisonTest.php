<?php

declare(strict_types=1);

namespace Cel\Tests\Runtime\Extension;

use Cel\Exception\EvaluationException;
use Cel\Exception\NoSuchKeyException;
use Cel\Exception\NoSuchOverloadException;
use Cel\Exception\UnsupportedOperationException;
use Cel\Span\Span;
use Cel\Tests\Runtime\RuntimeTestCase;
use Cel\Value\BooleanValue;
use Cel\Value\StringValue;
use Cel\Value\Value;
use Override;

use const NAN;

/**
 * Covers CEL proposal 210: numeric comparisons and null equality (heterogeneous
 * equality), and the heterogeneous `in` / map-indexing extensions.
 *
 */
final class HeterogeneousComparisonTest extends RuntimeTestCase
{
    /**
     * @return iterable<string, array{0: string, 1: array<string, mixed>, 2: Value|EvaluationException}>
     */
    #[Override]
    public static function provideEvaluationCases(): iterable
    {
        yield from self::provideNumericEqualityCases();
        yield from self::provideNumericComparisonCases();
        yield from self::provideNullEqualityCases();
        yield from self::provideTotalEqualityCases();
        yield from self::provideMembershipCases();
        yield from self::provideMapAccessCases();
        yield from self::provideNaNCases();
    }

    /**
     * @return iterable<string, array{0: string, 1: array<string, mixed>, 2: Value|EvaluationException}>
     */
    private static function provideNumericEqualityCases(): iterable
    {
        yield 'int equals double' => ['1 == 1.0', [], new BooleanValue(true)];
        yield 'int equals uint' => ['1 == 1u', [], new BooleanValue(true)];
        yield 'double equals uint' => ['1.0 == 1u', [], new BooleanValue(true)];
        yield 'int not equal double' => ['1 != 2.0', [], new BooleanValue(true)];
        yield 'double differs from int' => ['1.5 == 1', [], new BooleanValue(false)];
        yield 'uint equals double' => ['2u == 2.0', [], new BooleanValue(true)];
        yield 'uint differs from int' => ['2u == 3', [], new BooleanValue(false)];
        yield 'uint not equal int' => ['2u != 3', [], new BooleanValue(true)];
    }

    /**
     * @return iterable<string, array{0: string, 1: array<string, mixed>, 2: Value|EvaluationException}>
     */
    private static function provideNumericComparisonCases(): iterable
    {
        yield 'int less than double' => ['1 < 1.5', [], new BooleanValue(true)];
        yield 'int greater than double' => ['2 > 1.5', [], new BooleanValue(true)];
        yield 'int less-or-equal double' => ['1 <= 1.0', [], new BooleanValue(true)];
        yield 'int greater-or-equal double' => ['1 >= 1.0', [], new BooleanValue(true)];
        yield 'uint less than int' => ['1u < 2', [], new BooleanValue(true)];
        yield 'uint greater than int' => ['3u > 2', [], new BooleanValue(true)];
        yield 'double less than uint' => ['1.0 < 2u', [], new BooleanValue(true)];
        yield 'double greater than int' => ['2.5 > 2', [], new BooleanValue(true)];
        yield 'int less than uint' => ['1 < 2u', [], new BooleanValue(true)];
        yield 'negative int less than uint' => ['-1 < 1u', [], new BooleanValue(true)];
        yield 'uint less-or-equal int equal' => ['2u <= 2', [], new BooleanValue(true)];
        yield 'uint greater-or-equal int equal' => ['2u >= 2', [], new BooleanValue(true)];
        yield 'int not less than smaller uint' => ['5 < 2u', [], new BooleanValue(false)];
    }

    /**
     * @return iterable<string, array{0: string, 1: array<string, mixed>, 2: Value|EvaluationException}>
     */
    private static function provideNullEqualityCases(): iterable
    {
        yield 'int equals null is false' => ['0 == null', [], new BooleanValue(false)];
        yield 'null equals int is false' => ['null == 0', [], new BooleanValue(false)];
        yield 'null equals null' => ['null == null', [], new BooleanValue(true)];
        yield 'empty list not equal null' => ['[] != null', [], new BooleanValue(true)];
        yield 'empty map equals null is false' => ['{} == null', [], new BooleanValue(false)];
        yield 'null not equal string' => ['null != "x"', [], new BooleanValue(true)];
        yield 'string equals null is false' => ['"x" == null', [], new BooleanValue(false)];
    }

    /**
     * @return iterable<string, array{0: string, 1: array<string, mixed>, 2: Value|EvaluationException}>
     */
    private static function provideTotalEqualityCases(): iterable
    {
        yield 'int equals string is false' => ['1 == "1"', [], new BooleanValue(false)];
        yield 'string equals int is false' => ['"1" == 1', [], new BooleanValue(false)];
        yield 'bool equals int is false' => ['true == 1', [], new BooleanValue(false)];
        yield 'map equals int is false' => ['{} == 1', [], new BooleanValue(false)];
        yield 'int not equal string is true' => ['1 != "x"', [], new BooleanValue(true)];
        yield 'nested list cross-type equal' => ['[1, 2] == [1.0, 2.0]', [], new BooleanValue(true)];
        yield 'nested map cross-type equal' => ['{"a": 1} == {"a": 1.0}', [], new BooleanValue(true)];
        yield 'nested list unequal' => ['[1] == ["1"]', [], new BooleanValue(false)];
    }

    /**
     * @return iterable<string, array{0: string, 1: array<string, mixed>, 2: Value|EvaluationException}>
     */
    private static function provideMembershipCases(): iterable
    {
        // heterogeneous `in` over lists
        yield 'int in double list' => ['1 in [1.0, 2.5]', [], new BooleanValue(true)];
        yield 'int not in double list' => ['2 in [1.0, 2.5]', [], new BooleanValue(false)];
        yield 'double not in int list' => ['2.5 in [1, 2, 3]', [], new BooleanValue(false)];
        yield 'null not in int list' => ['null in [1, 2, 3]', [], new BooleanValue(false)];
        yield 'null in null list' => ['null in [null]', [], new BooleanValue(true)];
        yield 'uint in int list' => ['1u in [1, 2]', [], new BooleanValue(true)];

        // heterogeneous `in` over map keys
        yield 'int in int-keyed map' => ['1 in {1: "a"}', [], new BooleanValue(true)];
        yield 'double in int-keyed map' => ['1.0 in {1: "a"}', [], new BooleanValue(true)];
        yield 'int not in map' => ['2 in {1: "a"}', [], new BooleanValue(false)];
        yield 'uint in int-keyed map' => ['1u in {1: "a"}', [], new BooleanValue(true)];
        yield 'string in string-keyed map' => ['"k" in {"k": 1}', [], new BooleanValue(true)];
        yield 'non-integral double not in map' => ['1.5 in {1: "a"}', [], new BooleanValue(false)];
    }

    /**
     * @return iterable<string, array{0: string, 1: array<string, mixed>, 2: Value|EvaluationException}>
     */
    private static function provideMapAccessCases(): iterable
    {
        yield 'double index on int key' => ['{1: "hello"}[1.0]', [], new StringValue('hello')];
        yield 'uint index on int key' => ['{1: "hello"}[1u]', [], new StringValue('hello')];
        yield 'double index on uint key' => ['{1u: true}[1.0]', [], new BooleanValue(true)];
        yield 'int index on uint key' => ['{1u: "v"}[1]', [], new StringValue('v')];
        yield 'missing integer key' => [
            '{1: "x"}[2]',
            [],
            new NoSuchKeyException('Key `2` does not exist in map', new Span(0, 0)),
        ];
        yield 'non-integral double key is missing' => [
            '{1: "x"}[1.5]',
            [],
            new NoSuchKeyException('Key `float` does not exist in map', new Span(0, 0)),
        ];
        yield 'boolean is not a valid key type' => [
            '{1: "x"}[true]',
            [],
            new NoSuchOverloadException(
                'Map keys must be string, integer, unsigned integer, or double, got `bool`',
                new Span(0, 0),
            ),
        ];
    }

    /**
     * @return iterable<string, array{0: string, 1: array<string, mixed>, 2: Value|EvaluationException}>
     */
    private static function provideNaNCases(): iterable
    {
        yield 'NaN less than errors' => [
            'x < 1.0',
            ['x' => NAN],
            new UnsupportedOperationException('NaN values cannot be ordered', new Span(0, 0)),
        ];
        yield 'greater than NaN errors' => [
            '1.0 > x',
            ['x' => NAN],
            new UnsupportedOperationException('NaN values cannot be ordered', new Span(0, 0)),
        ];
        yield 'NaN equals NaN is false' => ['x == x', ['x' => NAN], new BooleanValue(false)];
        yield 'NaN equals number is false' => ['x == 1.0', ['x' => NAN], new BooleanValue(false)];
        yield 'NaN not equal number is true' => ['x != 1.0', ['x' => NAN], new BooleanValue(true)];
    }

    public function testEqualityIsIdempotent(): void
    {
        static::assertTrue($this->evaluate('1 == 1.0')->idempotent);
    }
}
