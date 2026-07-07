<?php

declare(strict_types=1);

namespace Cel\Util;

use function is_int;

/**
 * Overflow-checked 64-bit signed integer arithmetic.
 */
final readonly class IntegerMath
{
    private function __construct() {}

    public static function add(int $left, int $right): null|int
    {
        $result = $left + $right;

        // @mago-expect analysis:redundant-type-comparison (an overflowing result is promoted to float)
        // @mago-expect analysis:redundant-condition (an overflowing result is promoted to float)
        return is_int($result) ? $result : null;
    }

    public static function subtract(int $left, int $right): null|int
    {
        $result = $left - $right;

        // @mago-expect analysis:redundant-type-comparison (an overflowing result is promoted to float)
        // @mago-expect analysis:redundant-condition (an overflowing result is promoted to float)
        return is_int($result) ? $result : null;
    }

    public static function multiply(int $left, int $right): null|int
    {
        $result = $left * $right;

        // @mago-expect analysis:redundant-type-comparison (an overflowing result is promoted to float)
        // @mago-expect analysis:redundant-condition (an overflowing result is promoted to float)
        return is_int($result) ? $result : null;
    }

    public static function negate(int $value): null|int
    {
        $result = -$value;

        // @mago-expect analysis:redundant-type-comparison (an overflowing result is promoted to float)
        // @mago-expect analysis:redundant-condition (an overflowing result is promoted to float)
        return is_int($result) ? $result : null;
    }
}
