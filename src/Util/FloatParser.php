<?php

declare(strict_types=1);

namespace Cel\Util;

use function preg_match;

/**
 * Parses numeric strings into floats.
 *
 * @internal
 */
final readonly class FloatParser
{
    /**
     * Parses a numeric string into a float, returning null when the string is
     * not a valid float representation.
     *
     * Accepts an optional sign followed by a decimal integer, a decimal
     * fraction, or scientific notation (e.g. "42", "-3.14", ".5", "1e10");
     * anything else yields null.
     */
    public static function tryParse(string $value): null|float
    {
        if (1 !== preg_match('/^[+-]?(\d+([.]\d*)?([eE][+-]?\d+)?|[.]\d+([eE][+-]?\d+)?)$/', $value)) {
            return null;
        }

        return (float) $value; // @mago-expect analysis:invalid-type-cast (validated as numeric above)
    }
}
