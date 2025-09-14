<?php

declare(strict_types=1);

namespace Cel\Tests\Token;

use Cel\Token\Associativity;
use Cel\Token\Precedence;
use Cel\Token\TokenKind;
use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\TestCase;

final class TokenKindTest extends TestCase
{
    #[DataProvider('provideDelimiterCases')]
    public function testIsDelimiter(TokenKind $kind, bool $expected): void
    {
        static::assertSame($expected, $kind->isDelimiter());
    }

    /**
     * @return iterable<string, array{TokenKind, bool}>
     */
    public static function provideDelimiterCases(): iterable
    {
        yield 'LeftParen' => [TokenKind::LeftParenthesis, true];
        yield 'RightParen' => [TokenKind::RightParenthesis, true];
        yield 'LeftBracket' => [TokenKind::LeftBracket, true];
        yield 'RightBracket' => [TokenKind::RightBracket, true];
        yield 'LeftBrace' => [TokenKind::LeftBrace, true];
        yield 'RightBrace' => [TokenKind::RightBrace, true];
        yield 'Comma' => [TokenKind::Comma, true];
        yield 'Dot' => [TokenKind::Dot, true];
        yield 'Colon' => [TokenKind::Colon, true];
        yield 'Question' => [TokenKind::Question, true];
        yield 'Identifier' => [TokenKind::Identifier, false];
        yield 'Plus' => [TokenKind::Plus, false];
    }

    #[DataProvider('provideOperatorCases')]
    public function testIsOperator(TokenKind $kind, bool $expected): void
    {
        static::assertSame($expected, $kind->isOperator());
    }

    /**
     * @return iterable<string, array{TokenKind, bool}>
     */
    public static function provideOperatorCases(): iterable
    {
        yield 'Plus' => [TokenKind::Plus, true];
        yield 'Minus' => [TokenKind::Minus, true];
        yield 'Asterisk' => [TokenKind::Asterisk, true];
        yield 'Slash' => [TokenKind::Slash, true];
        yield 'Percent' => [TokenKind::Percent, true];
        yield 'Bang' => [TokenKind::Bang, true];
        yield 'Equal' => [TokenKind::Equal, true];
        yield 'NotEqual' => [TokenKind::NotEqual, true];
        yield 'Less' => [TokenKind::Less, true];
        yield 'LessOrEqual' => [TokenKind::LessOrEqual, true];
        yield 'Greater' => [TokenKind::Greater, true];
        yield 'GreaterOrEqual' => [TokenKind::GreaterOrEqual, true];
        yield 'DoubleAmpersand' => [TokenKind::DoubleAmpersand, true];
        yield 'DoublePipe' => [TokenKind::DoublePipe, true];
        yield 'Question' => [TokenKind::Question, true];
        yield 'In' => [TokenKind::In, true];
        yield 'Identifier' => [TokenKind::Identifier, false];
        yield 'LeftParen' => [TokenKind::LeftParenthesis, false];
    }

    public function testIsWhitespace(): void
    {
        static::assertTrue(TokenKind::Whitespace->isWhitespace());
        static::assertFalse(TokenKind::Identifier->isWhitespace());
    }

    public function testIsComment(): void
    {
        static::assertTrue(TokenKind::Comment->isComment());
        static::assertFalse(TokenKind::Identifier->isComment());
    }

    #[DataProvider('provideLiteralCases')]
    public function testIsLiteral(TokenKind $kind, bool $expected): void
    {
        static::assertSame($expected, $kind->isLiteral());
    }

    /**
     * @return iterable<string, array{TokenKind, bool}>
     */
    public static function provideLiteralCases(): iterable
    {
        yield 'Float' => [TokenKind::LiteralFloat, true];
        yield 'Int' => [TokenKind::LiteralInt, true];
        yield 'UInt' => [TokenKind::LiteralUInt, true];
        yield 'String' => [TokenKind::LiteralString, true];
        yield 'Bytes' => [TokenKind::BytesSequence, true];
        yield 'True' => [TokenKind::True, true];
        yield 'False' => [TokenKind::False, true];
        yield 'Null' => [TokenKind::Null, true];
        yield 'Identifier' => [TokenKind::Identifier, false];
        yield 'Plus' => [TokenKind::Plus, false];
    }

    #[DataProvider('provideKeywordCases')]
    public function testIsKeyword(TokenKind $kind, bool $expected): void
    {
        static::assertSame($expected, $kind->isKeyword());
    }

    /**
     * @return iterable<string, array{TokenKind, bool}>
     */
    public static function provideKeywordCases(): iterable
    {
        yield 'True' => [TokenKind::True, true];
        yield 'False' => [TokenKind::False, true];
        yield 'Null' => [TokenKind::Null, true];
        yield 'In' => [TokenKind::In, true];
        yield 'As' => [TokenKind::As, false]; // 'as' is reserved, not a keyword
        yield 'Identifier' => [TokenKind::Identifier, false];
    }

    #[DataProvider('provideReservedCases')]
    public function testIsReserved(TokenKind $kind, bool $expected): void
    {
        static::assertSame($expected, $kind->isReserved());
    }

    /**
     * @return iterable<string, array{TokenKind, bool}>
     */
    public static function provideReservedCases(): iterable
    {
        yield 'As' => [TokenKind::As, true];
        yield 'Break' => [TokenKind::Break, true];
        yield 'Const' => [TokenKind::Const, true];
        yield 'Continue' => [TokenKind::Continue, true];
        yield 'Else' => [TokenKind::Else, true];
        yield 'For' => [TokenKind::For, true];
        yield 'Function' => [TokenKind::Function, true];
        yield 'If' => [TokenKind::If, true];
        yield 'Import' => [TokenKind::Import, true];
        yield 'Let' => [TokenKind::Let, true];
        yield 'Loop' => [TokenKind::Loop, true];
        yield 'Package' => [TokenKind::Package, true];
        yield 'Namespace' => [TokenKind::Namespace, true];
        yield 'Return' => [TokenKind::Return, true];
        yield 'Var' => [TokenKind::Var, true];
        yield 'Void' => [TokenKind::Void, true];
        yield 'While' => [TokenKind::While, true];
        yield 'In' => [TokenKind::In, false]; // 'in' is a keyword
        yield 'Identifier' => [TokenKind::Identifier, false];
    }

    #[DataProvider('providePrecedenceCases')]
    public function testGetPrecedence(TokenKind $kind, null|Precedence $expected, bool $unary = false): void
    {
        static::assertSame($expected, $kind->getPrecedence($unary));
    }

    public function testGetPrecedenceDefaults(): void
    {
        static::assertSame(Precedence::Additive, TokenKind::Minus->getPrecedence());
    }

    /**
     * @return iterable<string, array{0: TokenKind, 1: Precedence|null, 2?: bool}>
     */
    public static function providePrecedenceCases(): iterable
    {
        // Conditional
        yield 'Question' => [TokenKind::Question, Precedence::Conditional];

        // Or
        yield 'DoublePipe' => [TokenKind::DoublePipe, Precedence::Or];

        // And
        yield 'DoubleAmpersand' => [TokenKind::DoubleAmpersand, Precedence::And];

        // Relation
        yield 'Equal' => [TokenKind::Equal, Precedence::Relation];
        yield 'NotEqual' => [TokenKind::NotEqual, Precedence::Relation];
        yield 'Less' => [TokenKind::Less, Precedence::Relation];
        yield 'LessOrEqual' => [TokenKind::LessOrEqual, Precedence::Relation];
        yield 'Greater' => [TokenKind::Greater, Precedence::Relation];
        yield 'GreaterOrEqual' => [TokenKind::GreaterOrEqual, Precedence::Relation];
        yield 'In' => [TokenKind::In, Precedence::Relation];

        // Additive
        yield 'Plus' => [TokenKind::Plus, Precedence::Additive];
        yield 'Minus (binary)' => [TokenKind::Minus, Precedence::Additive, false];
        yield 'Minus (binary, default)' => [TokenKind::Minus, Precedence::Additive];

        // Multiplicative
        yield 'Asterisk' => [TokenKind::Asterisk, Precedence::Multiplicative];
        yield 'Slash' => [TokenKind::Slash, Precedence::Multiplicative];
        yield 'Percent' => [TokenKind::Percent, Precedence::Multiplicative];

        // Unary
        yield 'Minus (unary)' => [TokenKind::Minus, Precedence::Unary, true];
        yield 'Bang' => [TokenKind::Bang, Precedence::Unary];

        // Call
        yield 'LeftParen' => [TokenKind::LeftParenthesis, Precedence::Call];
        yield 'Dot' => [TokenKind::Dot, Precedence::Call];
        yield 'LeftBracket' => [TokenKind::LeftBracket, Precedence::Call];

        // No precedence
        yield 'Identifier' => [TokenKind::Identifier, null];
        yield 'LiteralInt' => [TokenKind::LiteralInt, null];
        yield 'RightParen' => [TokenKind::RightParenthesis, null];
    }
}
