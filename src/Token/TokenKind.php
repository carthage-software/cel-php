<?php

declare(strict_types=1);

namespace Cel\Token;

/**
 * Enumerates all possible kinds of tokens that can be produced by the CEL lexer.
 *
 * @mago-expect lint:too-many-enum-cases
 */
enum TokenKind
{
    // Delimiters and Operators
    case LeftParenthesis;
    case RightParenthesis;
    case LeftBracket;
    case RightBracket;
    case LeftBrace;
    case RightBrace;
    case Question;
    case Colon;
    case Comma;
    case DoubleAmpersand;
    case DoublePipe;
    case Dot;
    case Equal;
    case NotEqual;
    case Less;
    case LessOrEqual;
    case Greater;
    case GreaterOrEqual;
    case Plus;
    case Minus;
    case Asterisk;
    case Slash;
    case Percent;
    case Bang;

    // Literals
    case LiteralFloat;
    case LiteralInt;
    case LiteralUInt;
    case LiteralString;
    case BytesSequence;

    // Keywords
    case False;
    case True;
    case Null;
    case In;

    // Reserved words (cannot be identifiers)
    case As;
    case Break;
    case Const;
    case Continue;
    case Else;
    case For;
    case Function;
    case If;
    case Import;
    case Let;
    case Loop;
    case Package;
    case Namespace;
    case Return;
    case Var;
    case Void;
    case While;

    // Special Tokens
    case Identifier;
    case Whitespace;
    case Comment;
    case Unrecognized;

    public function isDelimiter(): bool
    {
        return match ($this) {
            TokenKind::LeftParenthesis,
            TokenKind::RightParenthesis,
            TokenKind::LeftBracket,
            TokenKind::RightBracket,
            TokenKind::LeftBrace,
            TokenKind::RightBrace,
            TokenKind::Comma,
            TokenKind::Dot,
            TokenKind::Colon,
            TokenKind::Question,
                => true,
            default => false,
        };
    }

    public function isOperator(): bool
    {
        return match ($this) {
            TokenKind::Plus,
            TokenKind::Minus,
            TokenKind::Asterisk,
            TokenKind::Slash,
            TokenKind::Percent,
            TokenKind::Bang,
            TokenKind::Equal,
            TokenKind::NotEqual,
            TokenKind::Less,
            TokenKind::LessOrEqual,
            TokenKind::Greater,
            TokenKind::GreaterOrEqual,
            TokenKind::DoubleAmpersand,
            TokenKind::DoublePipe,
            TokenKind::Question,
            TokenKind::In,
                => true,
            default => false,
        };
    }

    public function isWhitespace(): bool
    {
        return $this === TokenKind::Whitespace;
    }

    public function isComment(): bool
    {
        return $this === TokenKind::Comment;
    }

    public function isLiteral(): bool
    {
        return match ($this) {
            TokenKind::LiteralFloat,
            TokenKind::LiteralInt,
            TokenKind::LiteralUInt,
            TokenKind::LiteralString,
            TokenKind::BytesSequence,
            TokenKind::True,
            TokenKind::False,
            TokenKind::Null,
                => true,
            default => false,
        };
    }

    public function isKeyword(): bool
    {
        return match ($this) {
            TokenKind::False, TokenKind::True, TokenKind::Null, TokenKind::In => true,
            default => false,
        };
    }

    public function isReserved(): bool
    {
        return match ($this) {
            TokenKind::As,
            TokenKind::Break,
            TokenKind::Const,
            TokenKind::Continue,
            TokenKind::Else,
            TokenKind::For,
            TokenKind::Function,
            TokenKind::If,
            TokenKind::Import,
            TokenKind::Let,
            TokenKind::Loop,
            TokenKind::Package,
            TokenKind::Namespace,
            TokenKind::Return,
            TokenKind::Var,
            TokenKind::Void,
            TokenKind::While,
                => true,
            default => false,
        };
    }

    /**
     * Get the precedence of the token if it is an operator.
     *
     * @param bool $unary Whether to get the precedence for a unary operator.
     *                    This is only relevant for the `-` operator.
     *
     * @return Precedence|null The precedence of the operator, or null if it is not an operator.
     */
    public function getPrecedence(bool $unary = false): null|Precedence
    {
        return match ($this) {
            // `?:`
            self::Question => Precedence::Conditional,
            // `||`
            self::DoublePipe => Precedence::Or,
            // `&&`
            self::DoubleAmpersand => Precedence::And,
            // `==`, `!=`, `<`, `>`, `<=`, `>=`, `in`
            self::Equal,
            self::NotEqual,
            self::Less,
            self::LessOrEqual,
            self::Greater,
            self::GreaterOrEqual,
            self::In,
                => Precedence::Relation,
            // `+`, `-` (binary)
            self::Plus => Precedence::Additive,
            self::Minus => $unary ? Precedence::Unary : Precedence::Additive,
            // `*`, `/`, `%`
            self::Asterisk, self::Slash, self::Percent => Precedence::Multiplicative,
            // `-` (unary), `!`
            self::Bang => Precedence::Unary,
            // `()`, `.`, `[]`
            self::LeftParenthesis, self::Dot, self::LeftBracket => Precedence::Call,
            default => null,
        };
    }
}
