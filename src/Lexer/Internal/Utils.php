<?php

declare(strict_types=1);

namespace Cel\Lexer\Internal;

use Cel\Input\InputInterface;
use Cel\Token\TokenKind;
use Psl\Str;
use Psl\Str\Byte;

use function ctype_alnum;
use function ctype_alpha;
use function ctype_digit;

/**
 * @internal
 *
 * A helper class containing static methods to handle complex tokenization logic,
 * separating it from the main Lexer class.
 */
final readonly class Utils
{
    /**
     * @var array<non-empty-string, TokenKind> A map of keywords and reserved words to their respective token kinds.
     */
    private const array KEYWORDS = [
        'true' => TokenKind::True,
        'false' => TokenKind::False,
        'null' => TokenKind::Null,
        'in' => TokenKind::In,
        'as' => TokenKind::As,
        'break' => TokenKind::Break,
        'const' => TokenKind::Const,
        'continue' => TokenKind::Continue,
        'else' => TokenKind::Else,
        'for' => TokenKind::For,
        'function' => TokenKind::Function,
        'if' => TokenKind::If,
        'import' => TokenKind::Import,
        'let' => TokenKind::Let,
        'loop' => TokenKind::Loop,
        'package' => TokenKind::Package,
        'namespace' => TokenKind::Namespace,
        'return' => TokenKind::Return,
        'var' => TokenKind::Var,
        'void' => TokenKind::Void,
        'while' => TokenKind::While,
    ];

    public static function isAtNumberLiteral(InputInterface $input): bool
    {
        $char = $input->peek(0, 1);
        if ($char === '-' || $char === '.') {
            return ctype_digit($input->peek(1, 1));
        }

        return ctype_digit($char);
    }

    public static function isAtStringLiteral(InputInterface $input): bool
    {
        $c1 = Byte\lowercase($input->peek(0, 1));

        if ($c1 === '\'' || $c1 === '"') {
            return true;
        }

        if ($c1 === 'r' || $c1 === 'b') {
            $c2 = Byte\lowercase($input->peek(1, 1));
            if ($c2 === '\'' || $c2 === '"') {
                return true;
            }

            if ($c1 === 'r' && $c2 === 'b' || $c1 === 'b' && $c2 === 'r') {
                $c3 = $input->peek(2, 1);

                return $c3 === '\'' || $c3 === '"';
            }
        }

        return false;
    }

    public static function isAtIdentifier(InputInterface $input): bool
    {
        $char = $input->peek(0, 1);

        return ctype_alpha($char) || $char === '_';
    }

    /**
     * @return list{TokenKind, string}
     */
    public static function readNumberLiteral(InputInterface $input): array
    {
        $length = 0;
        $isFloat = false;

        // Peek at leading sign
        if ($input->peek($length, 1) === '-') {
            $length++;
        }

        // Check for prefixes (0x, 0o, 0b)
        if ($input->peek($length, 1) === '0' && ctype_alpha($input->peek($length + 1, 1))) {
            $prefix = Byte\lowercase($input->peek($length + 1, 1));
            $consumed = self::readPrefixedInteger($input, $prefix, $length);
            if ($consumed > 0) {
                $length = $consumed;
            } else {
                // Fallthrough to read as decimal/float (e.g., "0e1")
                [$length, $isFloat] = self::readDecimalOrFloat($input, $length);
            }
        } else {
            [$length, $isFloat] = self::readDecimalOrFloat($input, $length);
        }

        $kind = $isFloat ? TokenKind::LiteralFloat : TokenKind::LiteralInt;

        // Check for uint suffix, but only if not a float
        $suffix = Byte\lowercase($input->peek($length, 1));
        if (!$isFloat && $suffix === 'u') {
            $length++;
            $kind = TokenKind::LiteralUInt;
        }

        $value = $input->consume($length);

        return [$kind, $value];
    }

    /**
     * @return list{TokenKind, string}
     */
    public static function readStringLiteral(InputInterface $input): array
    {
        $prefix = null;
        $char = $input->peek(0, 1);
        if (Byte\lowercase($char) === 'r' || Byte\lowercase($char) === 'b') {
            $prefix = $char;
        }

        $scan_offset = $prefix !== null ? 1 : 0;
        $quote = $input->peek($scan_offset, 1);
        $is_triple = $input->peek($scan_offset + 1, 2) === $quote . $quote;
        $terminator = $is_triple ? Str\repeat($quote, 3) : $quote;
        $is_raw = Byte\lowercase($prefix ?? '') === 'r';
        $initial_offset = $scan_offset + Byte\length($terminator);

        $final_offset = self::consumeLiteralString($input, $terminator, $is_raw, $initial_offset);

        $value = $input->consume($final_offset);
        $kind = Byte\lowercase($prefix ?? '') === 'b' ? TokenKind::BytesSequence : TokenKind::LiteralString;

        return [$kind, $value];
    }

    /**
     * @return list{TokenKind, string}
     */
    public static function readIdentifier(InputInterface $input): array
    {
        $length = 1;
        while (true) {
            $char = $input->peek($length, 1);
            if ($char === '' || !ctype_alnum($char) && $char !== '_') {
                break;
            }

            $length++;
        }

        $value = $input->consume($length);
        $kind = self::KEYWORDS[$value] ?? TokenKind::Identifier;

        return [$kind, $value];
    }

    /**
     * @param int<0, max> $scan_offset
     * @return int<0, max>
     */
    private static function consumeLiteralString(
        InputInterface $input,
        string $terminator,
        bool $is_raw,
        int $scan_offset,
    ): int {
        $peeked = $input->peek($scan_offset, 1);

        // Base case: Unterminated string
        if ($peeked === '') {
            return $scan_offset;
        }

        // Base case: Found the terminator
        if ($input->peek($scan_offset, Byte\length($terminator)) === $terminator) {
            return $scan_offset + Byte\length($terminator);
        }

        // Recursive step
        if ($peeked === '\\' && !$is_raw) {
            // If the next character after the backslash is the end of the input, it's a dangling backslash.
            if ($input->peek($scan_offset + 1, 1) === '') {
                return $scan_offset + 1;
            }

            // Skip the backslash and the character after it.
            return self::consumeLiteralString($input, $terminator, $is_raw, $scan_offset + 2);
        }

        // Move to the next character.
        return self::consumeLiteralString($input, $terminator, $is_raw, $scan_offset + 1);
    }

    /**
     * @param int<0, max> $length
     *
     * @return array{int<0, max>, bool}
     */
    private static function readDecimalOrFloat(InputInterface $input, int $length): array
    {
        $is_float = false;
        $start_length = $length;
        // Peek integer part
        while (ctype_digit($input->peek($length, 1))) {
            $length++;
        }

        // Handle fractional part
        if ($input->peek($length, 1) === '.' && ctype_digit($input->peek($length + 1, 1))) {
            $is_float = true;
            $length++;
            while (ctype_digit($input->peek($length, 1))) {
                $length++;
            }
        }

        // Exponent is only valid if we had some digits before it
        if ($length > $start_length || $is_float) {
            // Handle exponent part
            $peeked_e = $input->peek($length, 1);
            if ($peeked_e === 'e' || $peeked_e === 'E') {
                $is_float = true;
                $length++;
                $peeked_sign = $input->peek($length, 1);
                if ($peeked_sign === '+' || $peeked_sign === '-') {
                    $length++;
                }

                while (ctype_digit($input->peek($length, 1))) {
                    $length++;
                }
            }
        }

        return [$length, $is_float];
    }

    /**
     * @param int<0, max> $length
     *
     * @return int<0, max>
     */
    private static function readPrefixedInteger(InputInterface $input, string $prefix, int $length): int
    {
        $fn = match ($prefix) {
            'x' => ctype_xdigit(...),
            'o' => fn(string $char): bool => $char >= '0' && $char <= '7',
            'b' => fn(string $char): bool => $char === '0' || $char === '1',
            default => null,
        };

        if ($fn === null) {
            return 0;
        }

        $length += 2;
        while ($fn($input->peek($length, 1))) {
            $length++;
        }

        return $length;
    }
}
