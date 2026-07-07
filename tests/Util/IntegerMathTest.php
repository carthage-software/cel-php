<?php

declare(strict_types=1);

namespace Cel\Tests\Util;

use Cel\Util\IntegerMath;
use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\TestCase;

use function intdiv;

use const PHP_INT_MAX;
use const PHP_INT_MIN;

final class IntegerMathTest extends TestCase
{
    /**
     * @param null|int $expected The result, or null when the operation overflows.
     */
    #[DataProvider('provideAddCases')]
    public function testAdd(null|int $expected, int $left, int $right): void
    {
        static::assertSame($expected, IntegerMath::add($left, $right));
    }

    /**
     * @return iterable<string, array{null|int, int, int}>
     */
    public static function provideAddCases(): iterable
    {
        yield 'small positives' => [5, 2, 3];
        yield 'mixed signs' => [-1, 4, -5];
        yield 'zero right' => [PHP_INT_MAX, PHP_INT_MAX, 0];
        yield 'max minus one plus one' => [PHP_INT_MAX, PHP_INT_MAX - 1, 1];
        yield 'max plus one overflows' => [null, PHP_INT_MAX, 1];
        yield 'max plus negative is fine' => [PHP_INT_MAX - 1, PHP_INT_MAX, -1];
        yield 'min plus one is fine' => [PHP_INT_MIN + 1, PHP_INT_MIN, 1];
        yield 'min plus minus one overflows' => [null, PHP_INT_MIN, -1];
        yield 'boundary at max minus five plus five' => [PHP_INT_MAX, PHP_INT_MAX - 5, 5];
        yield 'one over max with positive right' => [null, PHP_INT_MAX - 5, 6];
        yield 'boundary at min plus five minus five' => [PHP_INT_MIN, PHP_INT_MIN + 5, -5];
        yield 'one over min with negative right' => [null, PHP_INT_MIN + 5, -6];
    }

    /**
     * @param null|int $expected The result, or null when the operation overflows.
     */
    #[DataProvider('provideSubtractCases')]
    public function testSubtract(null|int $expected, int $left, int $right): void
    {
        static::assertSame($expected, IntegerMath::subtract($left, $right));
    }

    /**
     * @return iterable<string, array{null|int, int, int}>
     */
    public static function provideSubtractCases(): iterable
    {
        yield 'small' => [-10, 10, 20];
        yield 'zero right' => [PHP_INT_MIN, PHP_INT_MIN, 0];
        yield 'max minus negative one overflows' => [null, PHP_INT_MAX, -1];
        yield 'max minus one minus negative one is fine' => [PHP_INT_MAX, PHP_INT_MAX - 1, -1];
        yield 'min minus one overflows' => [null, PHP_INT_MIN, 1];
        yield 'min plus one minus one is fine' => [PHP_INT_MIN, PHP_INT_MIN + 1, 1];
        yield 'max minus positive is fine' => [PHP_INT_MAX - 1, PHP_INT_MAX, 1];
        yield 'boundary max minus five minus negative five' => [PHP_INT_MAX, PHP_INT_MAX - 5, -5];
        yield 'one over max with negative right' => [null, PHP_INT_MAX - 5, -6];
        yield 'boundary min plus five minus five' => [PHP_INT_MIN, PHP_INT_MIN + 5, 5];
        yield 'one over min with positive right' => [null, PHP_INT_MIN + 5, 6];
    }

    /**
     * @param null|int $expected The result, or null when the operation overflows.
     */
    #[DataProvider('provideMultiplyCases')]
    public function testMultiply(null|int $expected, int $left, int $right): void
    {
        static::assertSame($expected, IntegerMath::multiply($left, $right));
    }

    /**
     * @return iterable<string, array{null|int, int, int}>
     */
    public static function provideMultiplyCases(): iterable
    {
        yield 'small positives' => [12, 3, 4];
        yield 'positive times negative' => [-42, 6, -7];
        yield 'both negative' => [12, -3, -4];
        yield 'zero left' => [0, 0, PHP_INT_MAX];
        yield 'zero right' => [0, PHP_INT_MAX, 0];
        yield 'identity' => [PHP_INT_MAX, PHP_INT_MAX, 1];
        yield 'largest exact square' => [9_223_372_030_926_249_001, 3_037_000_499, 3_037_000_499];
        yield 'negative one times min overflows' => [null, -1, PHP_INT_MIN];
        yield 'min times negative one overflows' => [null, PHP_INT_MIN, -1];
        yield 'positive times positive overflows' => [null, PHP_INT_MAX, 2];
        yield 'positive times negative overflows' => [null, PHP_INT_MAX, -2];
        yield 'negative times positive overflows' => [null, -2, PHP_INT_MAX];
        yield 'min times positive overflows' => [null, PHP_INT_MIN, 2];
        yield 'negative times negative overflows' => [null, PHP_INT_MIN, -2];
        yield 'boundary positive product' => [PHP_INT_MAX - 1, intdiv(PHP_INT_MAX, 2), 2];
        yield 'one over positive product' => [null, intdiv(PHP_INT_MAX, 2) + 1, 2];
        yield 'boundary positive times negative' => [PHP_INT_MIN, 2, intdiv(PHP_INT_MIN, 2)];
        yield 'one over positive times negative' => [null, 2, intdiv(PHP_INT_MIN, 2) - 1];
        yield 'boundary negative times positive' => [PHP_INT_MIN, intdiv(PHP_INT_MIN, 2), 2];
        yield 'one over negative times positive' => [null, intdiv(PHP_INT_MIN, 2) - 1, 2];
        yield 'boundary negative times negative' => [PHP_INT_MAX - 1, -2, intdiv(PHP_INT_MAX, -2)];
        yield 'one over negative times negative' => [null, -2, intdiv(PHP_INT_MAX, -2) - 1];
    }

    /**
     * @param null|int $expected The result, or null when the operation overflows.
     */
    #[DataProvider('provideNegateCases')]
    public function testNegate(null|int $expected, int $value): void
    {
        static::assertSame($expected, IntegerMath::negate($value));
    }

    /**
     * @return iterable<string, array{null|int, int}>
     */
    public static function provideNegateCases(): iterable
    {
        yield 'positive' => [-5, 5];
        yield 'negative' => [5, -5];
        yield 'zero' => [0, 0];
        yield 'max' => [-PHP_INT_MAX, PHP_INT_MAX];
        yield 'min overflows' => [null, PHP_INT_MIN];
    }
}
