<?php

declare(strict_types=1);

namespace Cel\Parser;

use Cel\Exception\InternalException;
use Cel\Util\NumberBase;
use InvalidArgumentException;

use function chr;
use function mb_chr;
use function sprintf;
use function strlen;
use function substr;

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
        $length = strlen($value);
        $position = 0;

        while ($position < $length) {
            $char = $value[$position];

            if ('\\' !== $char) {
                $result .= $char;
                $position++;
                continue;
            }

            // We have a backslash, check next character
            if (($position + 1) >= $length) {
                throw InternalException::forMessage('Incomplete escape sequence at end of string');
            }

            $next = $value[$position + 1];

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
                default => throw InternalException::forMessage(sprintf('Invalid escape sequence \\%s', $next)),
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
        $length = strlen($value);
        $position = 0;

        while ($position < $length) {
            $char = $value[$position];

            if ('\\' !== $char) {
                $result .= $char;
                $position++;
                continue;
            }

            if (($position + 1) >= $length) {
                throw InternalException::forMessage('Incomplete escape sequence at end of bytes literal');
            }

            $next = $value[$position + 1];

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
                default => throw InternalException::forMessage(sprintf(
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
     * @param int<0, max> $position Current position reference
     * @param string $replacement The character to return
     *
     * @return string The replacement character
     */
    private static function simpleEscape(int &$position, string $replacement): string
    {
        $position += 2; // Skip backslash and escape character
        return $replacement;
    }

    /**
     * Processes Unicode escape sequence \uHHHH or \UHHHHHHHH.
     *
     * @param string $value The full string
     * @param int<0, max> $position Current position reference (at backslash)
     * @param int<4, 8> $hexDigits Number of hex digits (4 or 8)
     *
     * @return string The UTF-8 encoded character
     *
     * @throws InternalException If the escape sequence is invalid
     */
    private static function unescapeUnicode(string $value, int &$position, int $hexDigits): string
    {
        $start = $position + 2; // Skip \u or \U
        $hexString = substr($value, $start, $hexDigits);

        if ('' === $hexString || strlen($hexString) < $hexDigits) {
            throw InternalException::forMessage(sprintf(
                'Invalid Unicode escape: expected %d hex digits after \\%s',
                $hexDigits,
                $hexDigits === 4 ? 'u' : 'U',
            ));
        }

        try {
            $codePoint = (int) NumberBase::baseConvert($hexString, 16, 10);
        } catch (InvalidArgumentException $e) {
            throw InternalException::forMessage(
                sprintf('Invalid Unicode escape: expected valid hex digits after \\%s', $hexDigits === 4 ? 'u' : 'U'),
                $e,
            );
        }

        // Validate code point range
        if ($codePoint > 0x10_FFFF) {
            throw InternalException::forMessage(sprintf(
                'Invalid Unicode code point: U+%X is out of range',
                $codePoint,
            ));
        }

        // Check for surrogate pairs (U+D800 to U+DFFF are invalid)
        if ($codePoint >= 0xD800 && $codePoint <= 0xDFFF) {
            throw InternalException::forMessage(sprintf('Invalid Unicode code point: U+%X is a surrogate', $codePoint));
        }

        $position += 2 + $hexDigits;

        return mb_chr($codePoint);
    }

    /**
     * Processes hex escape sequence \xHH or \XHH.
     *
     * @param string $value The full string
     * @param int<0, max> $position Current position reference (at backslash)
     * @param bool $isString True for strings (Unicode code point), false for bytes (raw octet)
     *
     * @return string The result
     *
     * @throws InternalException If the escape sequence is invalid
     */
    private static function unescapeHex(string $value, int &$position, bool $isString): string
    {
        $start = $position + 2; // Skip \x or \X
        $hexString = substr($value, $start, 2);

        if ('' === $hexString || strlen($hexString) !== 2) {
            throw InternalException::forMessage('Invalid hex escape: expected 2 hex digits after \\x');
        }

        try {
            $byteValue = (int) NumberBase::baseConvert($hexString, 16, 10);
        } catch (InvalidArgumentException $e) {
            throw InternalException::forMessage('Invalid hex escape: expected valid hex digits after \\x', $e);
        }

        $position += 4; // \xHH = 4 chars

        if ($isString) {
            // For strings, treat as Unicode code point and encode as UTF-8
            return mb_chr($byteValue);
        }

        // For bytes, return raw octet
        return chr($byteValue);
    }

    /**
     * Processes octal escape sequence \OOO.
     *
     * @param string $value The full string
     * @param int<0, max> $position Current position reference (at backslash)
     * @param bool $isString True for strings (Unicode code point), false for bytes (raw octet)
     *
     * @return string The result
     *
     * @throws InternalException If the escape sequence is invalid
     */
    private static function unescapeOctal(string $value, int &$position, bool $isString): string
    {
        // Read up to 3 octal digits
        $start = $position + 1; // Skip backslash
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

        $octalValue = (int) NumberBase::baseConvert($octalString, 8, 10);

        // Octal must be in range 000-377 (0-255)
        if ($octalValue > 255) {
            throw InternalException::forMessage(sprintf('Invalid octal escape: \\%s exceeds 377', $octalString));
        }

        $position += 1 + strlen($octalString); // Update position

        if ($isString) {
            // For strings, treat as Unicode code point
            return mb_chr($octalValue);
        }

        // For bytes, return raw octet
        return chr($octalValue);
    }
}
