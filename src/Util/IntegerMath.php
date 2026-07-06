<?php

declare(strict_types=1);

namespace Cel\Util;

use function intdiv;

use const PHP_INT_MAX;
use const PHP_INT_MIN;

/**
 * Overflow-checked 64-bit signed integer arithmetic.
 */
final readonly class IntegerMath
{
    private function __construct() {}

    public static function add(int $left, int $right): null|int
    {
        if ($right > 0 && $left > (PHP_INT_MAX - $right) || $right < 0 && $left < (PHP_INT_MIN - $right)) {
            return null;
        }

        return $left + $right;
    }

    public static function subtract(int $left, int $right): null|int
    {
        if ($right < 0 && $left > (PHP_INT_MAX + $right) || $right > 0 && $left < (PHP_INT_MIN + $right)) {
            return null;
        }

        return $left - $right;
    }

    public static function multiply(int $left, int $right): null|int
    {
        $overflows =
            -1 === $left && PHP_INT_MIN === $right
            || -1 === $right && PHP_INT_MIN === $left
            || $left > 0 && $right > 0 && $left > intdiv(PHP_INT_MAX, $right)
            || $left > 0 && $right < 0 && $right < intdiv(PHP_INT_MIN, $left)
            || $left < 0 && $right > 0 && $left < intdiv(PHP_INT_MIN, $right)
            || $left < 0 && $right < 0 && $right < intdiv(PHP_INT_MAX, $left);

        if ($overflows) {
            return null;
        }

        return $left * $right;
    }

    public static function negate(int $value): null|int
    {
        if (PHP_INT_MIN === $value) {
            return null;
        }

        return -$value;
    }
}
