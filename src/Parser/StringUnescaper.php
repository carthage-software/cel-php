<?php

declare(strict_types=1);

namespace Cel\Parser;

use Cel\Exception\InternalException;
use Psl\Math;
use Psl\Ref;
use Psl\Str;
use Psl\Str\Byte;

/**
 * Utility class for unescaping CEL string and bytes literals.
 *
 * Implements CEL escape sequence processing according to the specification:
 * https://github.com/google/cel-spec/blob/master/doc/langdef.md
 *
 * @internal
 */
final readonly class StringUnescaper
{
    /**
     * Unescapes a string literal.
     *
     * Processes escape sequences:
     * - Punctuation: \\, \?, \", \', \`
     * - Whitespace: \a, \b, \f, \n, \r, \t, \v
     * - Unicode: \uHHHH (4 hex), \UHHHHHHHH (8 hex)
     * - Hex: \xHH or \XHH (Unicode code point)
     * - Octal: \OOO (Unicode code point)
     *
     * @param string $value The escaped string (without quotes)
     *
     * @return string The unescaped string
     *
     * @throws InternalException If an invalid escape sequence is encountered
     */
    public static function unescapeString(string $value): string
    {
        $result = '';
        $length = Byte\length($value);
        /** @var Ref<int<0, max>> */
        $position = new Ref(0);

        while ($position->value < $length) {
            $char = $value[$position->value];

            if ('\\' !== $char) {
                $result .= $char;
                $position->value++;
                continue;
            }

            // We have a backslash, check next character
            if (($position->value + 1) >= $length) {
                throw InternalException::forMessage('Incomplete escape sequence at end of string');
            }

            $next = $value[$position->value + 1];

            $result .= match ($next) {
                // Punctuation
                '\\' => self::simpleEscape($position, '\\'),
                '?' => self::simpleEscape($position, '?'),
                '"' => self::simpleEscape($position, '"'),
                '\'' => self::simpleEscape($position, '\''),
                '`' => self::simpleEscape($position, '`'),
                // Whitespace
                'a' => self::simpleEscape($position, "\x07"), // bell
                'b' => self::simpleEscape($position, "\x08"), // backspace
                'f' => self::simpleEscape($position, "\x0C"), // form feed
                'n' => self::simpleEscape($position, "\n"), // line feed
                'r' => self::simpleEscape($position, "\r"), // carriage return
                't' => self::simpleEscape($position, "\t"), // horizontal tab
                'v' => self::simpleEscape($position, "\x0B"), // vertical tab
                // Unicode escapes
                'u' => self::unescapeUnicode($value, $position, 4),
                'U' => self::unescapeUnicode($value, $position, 8),
                // Hex escape (Unicode code point in strings)
                'x', 'X' => self::unescapeHex($value, $position, true),
                // Octal escape (Unicode code point in strings)
                '0', '1', '2', '3', '4', '5', '6', '7' => self::unescapeOctal($value, $position, true),
                default => throw InternalException::forMessage(Str\format('Invalid escape sequence \\%s', $next)),
            };
        }

        return $result;
    }

    /**
     * Unescapes a bytes literal.
     *
     * Similar to unescapeString() but \xHH and octal escapes produce
     * raw octet values instead of Unicode code points.
     *
     * @param string $value The escaped bytes string (without prefix and quotes)
     *
     * @return string The unescaped bytes string
     *
     * @throws InternalException If an invalid escape sequence is encountered
     */
    public static function unescapeBytes(string $value): string
    {
        $result = '';
        $length = Byte\length($value);
        /** @var Ref<int<0, max>> $position */
        $position = new Ref(0);

        while ($position->value < $length) {
            $char = $value[$position->value];

            if ('\\' !== $char) {
                $result .= $char;
                $position->value++;
                continue;
            }

            if (($position->value + 1) >= $length) {
                throw InternalException::forMessage('Incomplete escape sequence at end of bytes literal');
            }

            $next = $value[$position->value + 1];

            $result .= match ($next) {
                // Punctuation
                '\\' => self::simpleEscape($position, '\\'),
                '?' => self::simpleEscape($position, '?'),
                '"' => self::simpleEscape($position, '"'),
                '\'' => self::simpleEscape($position, '\''),
                '`' => self::simpleEscape($position, '`'),
                // Whitespace
                'a' => self::simpleEscape($position, "\x07"),
                'b' => self::simpleEscape($position, "\x08"),
                'f' => self::simpleEscape($position, "\x0C"),
                'n' => self::simpleEscape($position, "\n"),
                'r' => self::simpleEscape($position, "\r"),
                't' => self::simpleEscape($position, "\t"),
                'v' => self::simpleEscape($position, "\x0B"),
                // Unicode escapes (still produce UTF-8 encoded bytes)
                'u' => self::unescapeUnicode($value, $position, 4),
                'U' => self::unescapeUnicode($value, $position, 8),
                // Hex escape (raw octet in bytes)
                'x', 'X' => self::unescapeHex($value, $position, false),
                // Octal escape (raw octet in bytes)
                '0', '1', '2', '3', '4', '5', '6', '7' => self::unescapeOctal($value, $position, false),
                default => throw InternalException::forMessage(Str\format(
                    'Invalid escape sequence \\%s in bytes literal',
                    $next,
                )),
            };
        }

        return $result;
    }

    /**
     * Handles simple escape sequences (single character replacements).
     *
     * @param Ref<int<0, max>> $position Current position reference
     * @param string $replacement The character to return
     *
     * @return string The replacement character
     */
    private static function simpleEscape(Ref $position, string $replacement): string
    {
        $position->value += 2; // Skip backslash and escape character
        return $replacement;
    }

    /**
     * Processes Unicode escape sequence \uHHHH or \UHHHHHHHH.
     *
     * @param string $value The full string
     * @param Ref<int<0, max>> $position Current position reference (at backslash)
     * @param int<4, 8> $hexDigits Number of hex digits (4 or 8)
     *
     * @return string The UTF-8 encoded character
     *
     * @throws InternalException If the escape sequence is invalid
     */
    private static function unescapeUnicode(string $value, Ref $position, int $hexDigits): string
    {
        $start = $position->value + 2; // Skip \u or \U
        $hexString = Byte\slice($value, $start, $hexDigits);

        if ('' === $hexString || Byte\length($hexString) < $hexDigits) {
            throw InternalException::forMessage(Str\format(
                'Invalid Unicode escape: expected %d hex digits after \\%s',
                $hexDigits,
                $hexDigits === 4 ? 'u' : 'U',
            ));
        }

        try {
            $codePoint = (int) Math\base_convert($hexString, 16, 10);
        } catch (Math\Exception\InvalidArgumentException $e) {
            throw InternalException::forMessage(
                Str\format(
                    'Invalid Unicode escape: expected valid hex digits after \\%s',
                    $hexDigits === 4 ? 'u' : 'U',
                ),
                $e,
            );
        }

        // Validate code point range
        if ($codePoint > 0x10_FFFF) {
            throw InternalException::forMessage(Str\format(
                'Invalid Unicode code point: U+%X is out of range',
                $codePoint,
            ));
        }

        // Check for surrogate pairs (U+D800 to U+DFFF are invalid)
        if ($codePoint >= 0xD800 && $codePoint <= 0xDFFF) {
            throw InternalException::forMessage(Str\format(
                'Invalid Unicode code point: U+%X is a surrogate',
                $codePoint,
            ));
        }

        $position->value += 2 + $hexDigits;

        return Str\chr($codePoint);
    }

    /**
     * Processes hex escape sequence \xHH or \XHH.
     *
     * @param string $value The full string
     * @param Ref<int<0, max>> $position Current position reference (at backslash)
     * @param bool $isString True for strings (Unicode code point), false for bytes (raw octet)
     *
     * @return string The result
     *
     * @throws InternalException If the escape sequence is invalid
     */
    private static function unescapeHex(string $value, Ref $position, bool $isString): string
    {
        $start = $position->value + 2; // Skip \x or \X
        $hexString = Byte\slice($value, $start, 2);

        if ('' === $hexString || Byte\length($hexString) !== 2) {
            throw InternalException::forMessage('Invalid hex escape: expected 2 hex digits after \\x');
        }

        try {
            $byteValue = (int) Math\base_convert($hexString, 16, 10);
        } catch (Math\Exception\InvalidArgumentException $e) {
            throw InternalException::forMessage('Invalid hex escape: expected valid hex digits after \\x', $e);
        }

        $position->value += 4; // \xHH = 4 chars

        if ($isString) {
            // For strings, treat as Unicode code point and encode as UTF-8
            return Str\chr($byteValue);
        }

        // For bytes, return raw octet
        return Byte\chr($byteValue);
    }

    /**
     * Processes octal escape sequence \OOO.
     *
     * @param string $value The full string
     * @param Ref<int<0, max>> $position Current position reference (at backslash)
     * @param bool $isString True for strings (Unicode code point), false for bytes (raw octet)
     *
     * @return string The result
     *
     * @throws InternalException If the escape sequence is invalid
     */
    private static function unescapeOctal(string $value, Ref $position, bool $isString): string
    {
        // Read up to 3 octal digits
        $start = $position->value + 1; // Skip backslash
        $octalString = '';
        $maxDigits = 3;

        for ($j = 0; $j < $maxDigits; $j++) {
            if (!isset($value[$start + $j])) {
                break;
            }

            $digit = $value[$start + $j];
            if ($digit < '0' || $digit > '7') {
                break;
            }

            $octalString .= $digit;
        }

        if ($octalString === '') {
            throw InternalException::forMessage('Invalid octal escape: expected octal digits');
        }

        $octalValue = (int) Math\base_convert($octalString, 8, 10);

        // Octal must be in range 000-377 (0-255)
        if ($octalValue > 255) {
            throw InternalException::forMessage(Str\format('Invalid octal escape: \\%s exceeds 377', $octalString));
        }

        $position->value += 1 + Byte\length($octalString); // Update position

        if ($isString) {
            // For strings, treat as Unicode code point
            return Str\chr($octalValue);
        }

        // For bytes, return raw octet
        return Byte\chr($octalValue);
    }
}
