<?php

declare(strict_types=1);

namespace Cel\Tests\Lexer;

use Cel\Input\Input;
use Cel\Lexer\Lexer;
use Cel\Tests\ResourceProviderTrait;
use Cel\Token\TokenKind;
use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\TestCase;
use Psl\Iter;

final class LexerTest extends TestCase
{
    use ResourceProviderTrait;

    #[DataProvider('provideCelResources')]
    public function testTokenizationIsLossless(string $source): void
    {
        $lexer = new Lexer(new Input($source));
        $reconstructed = '';
        while (true) {
            $token = $lexer->advance();
            if ($token === null) {
                break;
            }

            $reconstructed .= $token->value;
        }

        static::assertSame($source, $reconstructed, 'Concatenated tokens do not match the original source string.');
    }

    /**
     * @param list<list{TokenKind, string, int, int}> $expectedTokens
     */
    #[DataProvider('provideTokenizationCases')]
    public function testTokenization(string $source, array $expectedTokens): void
    {
        $lexer = new Lexer(new Input($source));
        $tokens = [];
        while (true) {
            $token = $lexer->advance();
            if ($token === null) {
                break;
            }

            $tokens[] = $token;
        }

        static::assertCount(Iter\count($expectedTokens), $tokens);

        foreach ($expectedTokens as $i => $expected) {
            if (!isset($tokens[$i])) {
                static::fail("Missing token at index {$i}");
            }

            $actual = $tokens[$i];

            static::assertSame($expected[0], $actual->kind, "Token {$i} kind mismatch for '{$actual->value}'");
            static::assertSame($expected[1], $actual->value, "Token {$i} value mismatch");
            static::assertSame($expected[2], $actual->span->start, "Token {$i} start mismatch");
            static::assertSame($expected[3], $actual->span->end, "Token {$i} end mismatch");
        }
    }

    /**
     * @mago-expect lint:halstead
     *
     * @return iterable<string, list{string, list<list{TokenKind, string, int, int}>}>
     */
    public static function provideTokenizationCases(): iterable
    {
        // Delimiters and Operators
        yield 'Parentheses' => [
            '()',
            [
                [TokenKind::LeftParenthesis,  '(', 0, 1],
                [TokenKind::RightParenthesis, ')', 1, 2],
            ],
        ];
        yield 'Brackets' => [
            '[]',
            [
                [TokenKind::LeftBracket,  '[', 0, 1],
                [TokenKind::RightBracket, ']', 1, 2],
            ],
        ];
        yield 'Braces' => [
            '{}',
            [
                [TokenKind::LeftBrace,  '{', 0, 1],
                [TokenKind::RightBrace, '}', 1, 2],
            ],
        ];
        yield 'Single-char operators' => [
            '?.,:+-*/%!',
            [
                [TokenKind::Question, '?', 0, 1],
                [TokenKind::Dot,      '.', 1, 2],
                [TokenKind::Comma,    ',', 2, 3],
                [TokenKind::Colon,    ':', 3, 4],
                [TokenKind::Plus,     '+', 4, 5],
                [TokenKind::Minus,    '-', 5, 6],
                [TokenKind::Asterisk, '*', 6, 7],
                [TokenKind::Slash,    '/', 7, 8],
                [TokenKind::Percent,  '%', 8, 9],
                [TokenKind::Bang,     '!', 9, 10],
            ],
        ];
        yield 'Multi-char operators' => [
            '&& || == != <= >= < >',
            [
                [TokenKind::DoubleAmpersand, '&&', 0,  2],
                [TokenKind::Whitespace,      ' ',  2,  3],
                [TokenKind::DoublePipe,      '||', 3,  5],
                [TokenKind::Whitespace,      ' ',  5,  6],
                [TokenKind::Equal,           '==', 6,  8],
                [TokenKind::Whitespace,      ' ',  8,  9],
                [TokenKind::NotEqual,        '!=', 9,  11],
                [TokenKind::Whitespace,      ' ',  11, 12],
                [TokenKind::LessOrEqual,     '<=', 12, 14],
                [TokenKind::Whitespace,      ' ',  14, 15],
                [TokenKind::GreaterOrEqual,  '>=', 15, 17],
                [TokenKind::Whitespace,      ' ',  17, 18],
                [TokenKind::Less,            '<',  18, 19],
                [TokenKind::Whitespace,      ' ',  19, 20],
                [TokenKind::Greater,         '>',  20, 21],
            ],
        ];

        // Literals
        $tripleSingle = "'''a\"\"b'c'''";
        $tripleDouble = '"""a\'b"c"""';

        yield 'String single quote' => ["'hello'", [[TokenKind::LiteralString, "'hello'", 0, 7]]];
        yield 'String double quote' => ['"world"', [[TokenKind::LiteralString, '"world"', 0, 7]]];
        yield 'String triple single quote' => [$tripleSingle, [[TokenKind::LiteralString, $tripleSingle, 0, 12]]];
        yield 'String triple double quote' => [$tripleDouble, [[TokenKind::LiteralString, $tripleDouble, 0, 11]]];
        yield 'String with escapes' => ['"a\nb\tc"', [[TokenKind::LiteralString, '"a\nb\tc"', 0, 9]]];
        yield 'Raw string' => ['r"a\nb"', [[TokenKind::LiteralString, 'r"a\nb"', 0, 7]]];
        yield 'Bytes simple' => ['b"abc"', [[TokenKind::BytesSequence, 'b"abc"', 0, 6]]];
        yield 'Bytes with hex escape' => ['b"\x41"', [[TokenKind::BytesSequence, 'b"\x41"', 0, 7]]];
        yield 'Bytes with octal escape' => ['b"\101"', [[TokenKind::BytesSequence, 'b"\101"', 0, 7]]];
        yield 'Bytes with invalid escape' => ['b"\z"', [[TokenKind::BytesSequence, 'b"\z"', 0, 5]]];

        // Numbers
        yield 'Integer' => ['123', [[TokenKind::LiteralInt, '123', 0, 3]]];
        yield 'Negative Integer' => ['-45', [[TokenKind::LiteralInt, '-45', 0, 3]]];
        yield 'Hex Integer' => ['0xFA', [[TokenKind::LiteralInt, '0xFA', 0, 4]]];
        yield 'Octal Integer' => ['0o77', [[TokenKind::LiteralInt, '0o77', 0, 4]]];
        yield 'Binary Integer' => ['0b1101', [[TokenKind::LiteralInt, '0b1101', 0, 6]]];
        yield 'Unsigned Integer' => ['123u', [[TokenKind::LiteralUInt, '123u', 0, 4]]];
        yield 'Float' => ['3.14', [[TokenKind::LiteralFloat, '3.14', 0, 4]]];
        yield 'Float with exponent' => ['1.2e-5', [[TokenKind::LiteralFloat, '1.2e-5', 0, 6]]];
        yield 'Float starting with dot' => ['.5', [[TokenKind::LiteralFloat, '.5', 0, 2]]];
        yield 'Float with just exponent' => ['1e5', [[TokenKind::LiteralFloat, '1e5', 0, 3]]];

        // Keywords and Identifiers
        yield 'Keywords' => [
            'true false null in',
            [
                [TokenKind::True,       'true',  0,  4],
                [TokenKind::Whitespace, ' ',     4,  5],
                [TokenKind::False,      'false', 5,  10],
                [TokenKind::Whitespace, ' ',     10, 11],
                [TokenKind::Null,       'null',  11, 15],
                [TokenKind::Whitespace, ' ',     15, 16],
                [TokenKind::In,         'in',    16, 18],
            ],
        ];
        yield 'Reserved words' => [
            'if else for',
            [
                [TokenKind::If,         'if',   0, 2],
                [TokenKind::Whitespace, ' ',    2, 3],
                [TokenKind::Else,       'else', 3, 7],
                [TokenKind::Whitespace, ' ',    7, 8],
                [TokenKind::For,        'for',  8, 11],
            ],
        ];
        yield 'Identifier' => ['my_var', [[TokenKind::Identifier, 'my_var', 0, 6]]];
        yield 'Identifier starting with underscore' => ['_a', [[TokenKind::Identifier, '_a', 0, 2]]];

        // Comments and Whitespace
        yield 'Whitespace' => [" \t\n ", [[TokenKind::Whitespace, " \t\n ", 0, 4]]];
        yield 'Single-line comment' => ["// hello world\n", [[TokenKind::Comment, "// hello world\n", 0, 15]]];
        yield 'Comment at EOF' => ['// hello', [[TokenKind::Comment, '// hello', 0, 8]]];

        // Unrecognized
        yield 'Unrecognized character' => ['#', [[TokenKind::Unrecognized, '#', 0, 1]]];

        // Edge cases
        yield 'Empty input' => ['', []];
        yield 'Single character identifier' => ['a', [[TokenKind::Identifier, 'a', 0, 1]]];
        yield 'Single digit' => ['1', [[TokenKind::LiteralInt, '1', 0, 1]]];
        yield 'Dot not followed by digit' => [
            '.a',
            [
                [TokenKind::Dot,        '.', 0, 1],
                [TokenKind::Identifier, 'a', 1, 2],
            ],
        ];

        yield 'Single ampersand' => ['&', [[TokenKind::Unrecognized, '&', 0, 1]]];
        yield 'Single pipe' => ['|', [[TokenKind::Unrecognized, '|', 0, 1]]];

        yield 'Double bang' => [
            '!!',
            [
                [TokenKind::Bang, '!', 0, 1],
                [TokenKind::Bang, '!', 1, 2],
            ],
        ];
        yield 'Double greater' => [
            '>>',
            [
                [TokenKind::Greater, '>', 0, 1],
                [TokenKind::Greater, '>', 1, 2],
            ],
        ];
        yield 'Double right parenthesis' => [
            '))',
            [
                [TokenKind::RightParenthesis, ')', 0, 1],
                [TokenKind::RightParenthesis, ')', 1, 2],
            ],
        ];
        yield 'Double right bracket' => [
            ']]',
            [
                [TokenKind::RightBracket, ']', 0, 1],
                [TokenKind::RightBracket, ']', 1, 2],
            ],
        ];
        yield 'Double right brace' => [
            '}}',
            [
                [TokenKind::RightBrace, '}', 0, 1],
                [TokenKind::RightBrace, '}', 1, 2],
            ],
        ];

        yield 'Comment without newline' => ['//foo', [[TokenKind::Comment, '//foo', 0, 5]]];
        yield 'Comment with newline' => [
            "//foo\n1",
            [
                [TokenKind::Comment,    "//foo\n", 0, 6],
                [TokenKind::LiteralInt, '1',       6, 7],
            ],
        ];
        yield 'Equals sign followed by letter' => [
            '=a',
            [
                [TokenKind::Unrecognized, '=', 0, 1],
                [TokenKind::Identifier,   'a', 1, 2],
            ],
        ];
    }

    public function testLosslessTokenization(): void
    {
        $source = <<<CEL
        // Condition
        account.balance >= transaction.withdrawal
            || (account.overdraftProtection
            && account.overdraftLimit >= transaction.withdrawal - account.balance)

        // Object construction
        common.GeoPoint{ latitude: 10.0, longitude: -5.5 }

        // Function call
        max(3, 5, 7)

        // Literals
        "Hello, World!"
        b"\x48\x65\x6c\x6c\x6f"
        true false null
        3.14 42 0x2A 0o52 0b101010 23u
        CEL;

        $lexer = new Lexer(new Input($source));
        $reconstructed = '';
        while (true) {
            $token = $lexer->advance();
            if ($token === null) {
                break;
            }

            $reconstructed .= $token->value;
        }

        static::assertSame($source, $reconstructed, 'Concatenated tokens do not match the original source string.');
    }

    public function testGetInput(): void
    {
        $input = new Input('test');
        $lexer = new Lexer($input);
        static::assertSame($input, $lexer->getInput());
    }

    public function testCursorPositionAndHasReachedEnd(): void
    {
        $lexer = new Lexer(new Input('a b'));
        static::assertSame(0, $lexer->cursorPosition());
        static::assertFalse($lexer->hasReachedEnd());

        $lexer->advance(); // 'a'
        static::assertSame(1, $lexer->cursorPosition());
        static::assertFalse($lexer->hasReachedEnd());

        $lexer->advance(); // ' '
        static::assertSame(2, $lexer->cursorPosition());
        static::assertFalse($lexer->hasReachedEnd());

        $lexer->advance(); // 'b'
        static::assertSame(3, $lexer->cursorPosition());
        static::assertTrue($lexer->hasReachedEnd());

        static::assertNull($lexer->advance());
        static::assertTrue($lexer->hasReachedEnd());
    }
}
