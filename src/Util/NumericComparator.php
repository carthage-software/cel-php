<?php

declare(strict_types=1);

namespace Cel\Util;

use Cel\Exception\UnsupportedOperationException;
use Cel\Value\FloatValue;
use Cel\Value\IntegerValue;
use Cel\Value\UnsignedIntegerValue;
use Cel\Value\Value;

use function bccomp;
use function is_nan;

use const PHP_INT_MAX;
use const PHP_INT_MIN;

/**
 * Precision-correct comparison of CEL numeric values (int, uint, double) across
 * types.
 *
 * Values are treated as though they exist on a single continuous number line,
 * following the algorithm used by cel-go's `common/types/compare.go`:
 *
 * - two integers (signed and/or unsigned) are compared exactly as decimal strings;
 * - comparisons involving a double are range-checked before falling back to a
 *   double comparison, avoiding precision loss for values outside the double's
 *   integer range.
 *
 * @internal
 */
final readonly class NumericComparator
{
    /**
     * The maximum uint64 value, as a decimal string.
     */
    private const string MAX_UINT64 = '18446744073709551615';

    private function __construct() {}

    /**
     * Determines whether a value is numeric (int, uint, or double).
     */
    public static function isNumeric(Value $value): bool
    {
        return $value instanceof IntegerValue || $value instanceof UnsignedIntegerValue || $value instanceof FloatValue;
    }

    /**
     * Determines whether a value is a NaN double.
     */
    public static function isNaN(Value $value): bool
    {
        return $value instanceof FloatValue && is_nan($value->value);
    }

    /**
     * Determines numeric equality, treating NaN as equal to nothing (including itself).
     *
     * Precondition: both values are numeric.
     */
    public static function equals(Value $a, Value $b): bool
    {
        if (self::isNaN($a) || self::isNaN($b)) {
            return false;
        }

        return 0 === self::compare($a, $b);
    }

    /**
     * Orders two numeric values, returning -1, 0, or 1.
     *
     * Precondition: both values are numeric.
     *
     * @throws UnsupportedOperationException If either value is NaN (NaN cannot be ordered).
     */
    public static function order(Value $a, Value $b): int
    {
        if (self::isNaN($a) || self::isNaN($b)) {
            throw UnsupportedOperationException::forNaN();
        }

        return self::compare($a, $b);
    }

    /**
     * Compares two numeric values, returning -1, 0, or 1.
     *
     * Preconditions: both values are numeric and neither is NaN.
     */
    private static function compare(Value $a, Value $b): int
    {
        if ($a instanceof FloatValue && $b instanceof FloatValue) {
            return self::compareDouble($a->value, $b->value);
        }

        if ($a instanceof FloatValue && $b instanceof IntegerValue) {
            return self::compareDoubleInt($a->value, $b->value);
        }

        if ($a instanceof FloatValue && $b instanceof UnsignedIntegerValue) {
            return self::compareDoubleUint($a->value, (string) $b->value);
        }

        if ($a instanceof IntegerValue && $b instanceof FloatValue) {
            return -self::compareDoubleInt($b->value, $a->value);
        }

        if ($a instanceof UnsignedIntegerValue && $b instanceof FloatValue) {
            return -self::compareDoubleUint($b->value, (string) $a->value);
        }

        // Both are integers (signed and/or unsigned): compare exactly as decimal strings.
        // @mago-expect analysis:possibly-invalid-argument(2) - both raw values are integer decimals here.
        return bccomp((string) $a->getRawValue(), (string) $b->getRawValue(), 0);
    }

    private static function compareDoubleInt(float $d, int $i): int
    {
        if ($d < (float) PHP_INT_MIN) {
            return -1;
        }

        if ($d > (float) PHP_INT_MAX) {
            return 1;
        }

        return self::compareDouble($d, (float) $i);
    }

    private static function compareDoubleUint(float $d, string $u): int
    {
        if ($d < 0.0) {
            return -1;
        }

        if ($d > (float) self::MAX_UINT64) {
            return 1;
        }

        // @mago-expect analysis:invalid-type-cast - `$u` is always a numeric string.
        return self::compareDouble($d, (float) $u);
    }

    private static function compareDouble(float $a, float $b): int
    {
        if ($a < $b) {
            return -1;
        }

        if ($a > $b) {
            return 1;
        }

        return 0;
    }
}
