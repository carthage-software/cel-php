<?php

declare(strict_types=1);

namespace Cel\Util;

use Cel\Value\BooleanValue;
use Cel\Value\FloatValue;
use Cel\Value\IntegerValue;
use Cel\Value\StringValue;
use Cel\Value\UnsignedIntegerValue;
use Cel\Value\Value;

use function floor;
use function is_finite;
use function is_int;
use function is_numeric;
use function str_starts_with;
use function strlen;
use function substr;

use const PHP_INT_MAX;
use const PHP_INT_MIN;

/**
 * Encodes CEL map keys as native PHP array keys, and decodes them back.
 *
 * Every key is stored as a string carrying a type tag behind an invalid-UTF-8
 * byte (`\xFF`): booleans as `\xFFb:`, numbers as `\xFFn:`, strings as `\xFFs:`.
 * Because a CEL `string` is always valid UTF-8, a tagged key can never collide
 * with a real string key, and, being a string, PHP never coerces a numeric key
 * (`"1"`) into an integer array key. This keeps CEL's distinct key types apart
 * (`"1" != 1 != 1u != true`) while still supporting unsigned integers beyond
 * `PHP_INT_MAX`, which are kept as their decimal digits.
 *
 * Numeric keys are normalized to a canonical decimal integer, so `1`, `1u`, and
 * `1.0` (which are cross-type equal in CEL) address the same entry. That
 * normalization is one-way: a numeric key decodes back to an int or a uint by
 * magnitude, not to its original spelling.
 */
final readonly class MapKeyUtil
{
    private const string BOOLEAN_TAG = "\xFFb:";

    private const string NUMBER_TAG = "\xFFn:";

    private const string STRING_TAG = "\xFFs:";

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
            || $value instanceof BooleanValue
            || $value instanceof FloatValue
        );
    }

    /**
     * Encodes a plain string as the native array key that addresses its map
     * entry. Field-name selection (`map.field`) resolves to a string key.
     *
     * @return non-empty-string
     */
    public static function stringKey(string $value): string
    {
        return self::STRING_TAG . $value;
    }

    /**
     * Encodes a value as the native array key that addresses its map entry, or
     * null when it cannot be a key (a non-key type, or a non-integral or
     * out-of-range double).
     *
     * @return null|non-empty-string
     */
    public static function resolve(Value $value): null|string
    {
        if ($value instanceof StringValue) {
            return self::stringKey($value->value);
        }

        if ($value instanceof IntegerValue) {
            return self::NUMBER_TAG . $value->value;
        }

        if ($value instanceof UnsignedIntegerValue) {
            return self::NUMBER_TAG . $value->value;
        }

        if ($value instanceof BooleanValue) {
            return self::BOOLEAN_TAG . ($value->value ? '1' : '0');
        }

        if ($value instanceof FloatValue) {
            $integer = self::doubleToInt($value->value);

            return null === $integer ? null : self::NUMBER_TAG . $integer;
        }

        return null;
    }

    /**
     * Resolves a value to an integer position for list indexing (an int, a
     * uint within range, or an integral double), or null when it is not a valid
     * list index.
     */
    public static function resolveIndex(Value $value): null|int
    {
        if ($value instanceof IntegerValue) {
            return $value->value;
        }

        if ($value instanceof UnsignedIntegerValue) {
            return is_int($value->value) ? $value->value : null;
        }

        if ($value instanceof FloatValue) {
            return self::doubleToInt($value->value);
        }

        return null;
    }

    /**
     * Reconstructs the CEL value a native map key was encoded from.
     */
    public static function keyToValue(int|string $key): Value
    {
        if (is_int($key)) {
            return new IntegerValue($key);
        }

        if (str_starts_with($key, self::BOOLEAN_TAG)) {
            return new BooleanValue(self::BOOLEAN_TAG . '1' === $key);
        }

        if (str_starts_with($key, self::STRING_TAG)) {
            return new StringValue(substr($key, strlen(self::STRING_TAG)));
        }

        if (str_starts_with($key, self::NUMBER_TAG)) {
            $decimal = substr($key, strlen(self::NUMBER_TAG));
            $asInt = (int) $decimal;
            if ((string) $asInt === $decimal) {
                return new IntegerValue($asInt);
            }

            // A number that does not round-trip through an int is an unsigned
            // integer beyond the signed range, preserved as its decimal digits.
            return is_numeric($decimal) ? new UnsignedIntegerValue($decimal) : new StringValue($key);
        }

        return new StringValue($key);
    }

    /**
     * Decodes a native map key into a plain PHP array key, for exporting a map
     * as a native array. Booleans collapse to `1`/`0` and numbers to an int (or
     * a decimal string when they exceed the integer range), mirroring how PHP
     * itself would key such an array. Untagged keys are returned unchanged.
     */
    public static function keyToRaw(int|string $key): int|string
    {
        if (is_int($key)) {
            return $key;
        }

        if (str_starts_with($key, self::BOOLEAN_TAG)) {
            return self::BOOLEAN_TAG . '1' === $key ? 1 : 0;
        }

        if (str_starts_with($key, self::STRING_TAG)) {
            return substr($key, strlen(self::STRING_TAG));
        }

        if (str_starts_with($key, self::NUMBER_TAG)) {
            $decimal = substr($key, strlen(self::NUMBER_TAG));
            $asInt = (int) $decimal;

            return (string) $asInt === $decimal ? $asInt : $decimal;
        }

        return $key;
    }

    /**
     * Converts an integral double within the signed-integer range to an integer.
     * Returns null for non-integral, non-finite, or out-of-range doubles.
     */
    private static function doubleToInt(float $value): null|int
    {
        if (!is_finite($value) || floor($value) !== $value) {
            return null;
        }

        if ($value < (float) PHP_INT_MIN || $value > (float) PHP_INT_MAX) {
            return null;
        }

        return (int) $value;
    }
}
