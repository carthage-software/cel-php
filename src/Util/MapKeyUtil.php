<?php

declare(strict_types=1);

namespace Cel\Util;

use Cel\Value\FloatValue;
use Cel\Value\IntegerValue;
use Cel\Value\StringValue;
use Cel\Value\UnsignedIntegerValue;
use Cel\Value\Value;
use Psl\Math;

use function is_finite;

use const PHP_INT_MAX;
use const PHP_INT_MIN;

/**
 * Resolves CEL values to native array keys for map access.
 *
 * Supports heterogeneous numeric map keys: an integer-valued numeric index
 * (an int, a uint, or an integral double) resolves to the same integer key, so
 * that `{1: x}[1.0]`, `{1: x}[1u]`, and `{1u: x}[1]` all address the same entry.
 *
 * Because native PHP arrays cannot distinguish signed from unsigned integer keys
 * or hold boolean keys, those cel-go distinctions are not represented here.
 */
final readonly class MapKeyUtil
{
    private function __construct() {}

    /**
     * Determines whether a value's type may be used as a map key.
     */
    public static function isKeyType(Value $value): bool
    {
        return (
            $value instanceof StringValue
            || $value instanceof IntegerValue
            || $value instanceof UnsignedIntegerValue
            || $value instanceof FloatValue
        );
    }

    /**
     * Resolves a value to the native array key it should look up, or null when no
     * key could match (a non-integral or out-of-range double, or a non-key type).
     *
     * @return null|array-key
     */
    public static function resolve(Value $value): null|int|string
    {
        if ($value instanceof StringValue) {
            return $value->value;
        }

        if ($value instanceof IntegerValue) {
            return $value->value;
        }

        if ($value instanceof UnsignedIntegerValue) {
            return $value->value;
        }

        if ($value instanceof FloatValue) {
            return self::doubleToKey($value->value);
        }

        return null;
    }

    /**
     * Converts an integral double within the signed-integer range to an integer
     * key. Returns null for non-integral, non-finite, or out-of-range doubles.
     */
    private static function doubleToKey(float $value): null|int
    {
        if (!is_finite($value) || Math\floor($value) !== $value) {
            return null;
        }

        if ($value < (float) PHP_INT_MIN || $value > (float) PHP_INT_MAX) {
            return null;
        }

        return (int) $value;
    }
}
