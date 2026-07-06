<?php

declare(strict_types=1);

namespace Cel\Util;

use Psl\Math;

/**
 * Overflow-checked 64-bit signed integer arithmetic.
 */
final readonly class IntegerMath
{
    private function __construct() {}

    public static function add(int $left, int $right): null|int
    {
        if ($right > 0 && $left > (Math\INT64_MAX - $right) || $right < 0 && $left < (Math\INT64_MIN - $right)) {
            return null;
        }

        return $left + $right;
    }

    public static function subtract(int $left, int $right): null|int
    {
        if ($right < 0 && $left > (Math\INT64_MAX + $right) || $right > 0 && $left < (Math\INT64_MIN + $right)) {
            return null;
        }

        return $left - $right;
    }

    public static function multiply(int $left, int $right): null|int
    {
        $overflows =
            -1 === $left && Math\INT64_MIN === $right
            || -1 === $right && Math\INT64_MIN === $left
            || $left > 0 && $right > 0 && $left > Math\div(Math\INT64_MAX, $right)
            || $left > 0 && $right < 0 && $right < Math\div(Math\INT64_MIN, $left)
            || $left < 0 && $right > 0 && $left < Math\div(Math\INT64_MIN, $right)
            || $left < 0 && $right < 0 && $right < Math\div(Math\INT64_MAX, $left);

        if ($overflows) {
            return null;
        }

        return $left * $right;
    }

    public static function negate(int $value): null|int
    {
        if (Math\INT64_MIN === $value) {
            return null;
        }

        return -$value;
    }
}
