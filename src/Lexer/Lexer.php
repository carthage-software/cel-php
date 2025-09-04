<?php

declare(strict_types=1);

namespace Cel\Lexer;

use Cel\Input\InputInterface;
use Cel\Lexer\Internal\Utils;
use Cel\Span\Span;
use Cel\Token\Token;
use Cel\Token\TokenKind;
use Override;

use function ctype_space;

/**
 * A lexer for the Common Expression Language (CEL).
 *
 * This class is responsible for breaking the input source code into a stream of tokens.
 * It advances one token at a time and is designed to be used by a parser.
 *
 * @see https://github.com/google/cel-spec/blob/master/doc/langdef.md#syntax
 */
final readonly class Lexer implements LexerInterface
{
    /**
     * @param InputInterface $input The input stream to be tokenized.
     */
    public function __construct(
        private InputInterface $input,
    ) {}

    #[Override]
    public function getInput(): InputInterface
    {
        return $this->input;
    }

    /**
     * @return int<0, max> The current cursor position in the input.
     */
    #[Override]
    public function cursorPosition(): int
    {
        return $this->input->cursorPosition();
    }

    #[Override]
    public function hasReachedEnd(): bool
    {
        return $this->input->hasReachedEnd();
    }

    #[Override]
    public function advance(): null|Token
    {
        if ($this->hasReachedEnd()) {
            return null;
        }

        $start = $this->cursorPosition();
        $char = $this->input->read(1);

        // Handle whitespace
        if (ctype_space($char)) {
            $value = $this->input->consumeWhiteSpace();
            $end = $this->cursorPosition();
            return new Token(new Span($start, $end), TokenKind::Whitespace, $value);
        }

        // Handle comments
        if ($char === '/' && $this->input->peek(1, 1) === '/') {
            $value = $this->input->consumeUntil("\n");
            if (!$this->hasReachedEnd()) {
                $value .= $this->input->consume(1); // consume the newline as well
            }

            $end = $this->cursorPosition();

            return new Token(new Span($start, $end), TokenKind::Comment, $value);
        }

        [$kind, $value] = match (true) {
            // Literals and Identifiers (must be checked before operators)
            Utils::isAtNumberLiteral($this->input) => Utils::readNumberLiteral($this->input),
            Utils::isAtStringLiteral($this->input) => Utils::readStringLiteral($this->input),
            Utils::isAtIdentifier($this->input) => Utils::readIdentifier($this->input),
            // Multi-character operators
            $char === '&' && $this->input->peek(1, 1) === '&' => $this->consumeFixed(TokenKind::DoubleAmpersand, 2),
            $char === '|' && $this->input->peek(1, 1) === '|' => $this->consumeFixed(TokenKind::DoublePipe, 2),
            $char === '=' && $this->input->peek(1, 1) === '=' => $this->consumeFixed(TokenKind::Equal, 2),
            $char === '!' && $this->input->peek(1, 1) === '=' => $this->consumeFixed(TokenKind::NotEqual, 2),
            $char === '<' && $this->input->peek(1, 1) === '=' => $this->consumeFixed(TokenKind::LessOrEqual, 2),
            $char === '>' && $this->input->peek(1, 1) === '=' => $this->consumeFixed(TokenKind::GreaterOrEqual, 2),
            // Single-character operators and delimiters
            $char === '(' => $this->consumeFixed(TokenKind::LeftParenthesis, 1),
            $char === ')' => $this->consumeFixed(TokenKind::RightParenthesis, 1),
            $char === '[' => $this->consumeFixed(TokenKind::LeftBracket, 1),
            $char === ']' => $this->consumeFixed(TokenKind::RightBracket, 1),
            $char === '{' => $this->consumeFixed(TokenKind::LeftBrace, 1),
            $char === '}' => $this->consumeFixed(TokenKind::RightBrace, 1),
            $char === '.' => $this->consumeFixed(TokenKind::Dot, 1),
            $char === ',' => $this->consumeFixed(TokenKind::Comma, 1),
            $char === ':' => $this->consumeFixed(TokenKind::Colon, 1),
            $char === '?' => $this->consumeFixed(TokenKind::Question, 1),
            $char === '+' => $this->consumeFixed(TokenKind::Plus, 1),
            $char === '-' => $this->consumeFixed(TokenKind::Minus, 1),
            $char === '*' => $this->consumeFixed(TokenKind::Asterisk, 1),
            $char === '/' => $this->consumeFixed(TokenKind::Slash, 1),
            $char === '%' => $this->consumeFixed(TokenKind::Percent, 1),
            $char === '!' => $this->consumeFixed(TokenKind::Bang, 1),
            $char === '<' => $this->consumeFixed(TokenKind::Less, 1),
            $char === '>' => $this->consumeFixed(TokenKind::Greater, 1),
            // Default case for unrecognized characters
            default => $this->consumeFixed(TokenKind::Unrecognized, 1),
        };

        $end = $this->cursorPosition();

        return new Token(new Span($start, $end), $kind, $value);
    }

    /**
     * @param int<1, max> $length
     *
     * @return list{TokenKind, string}
     */
    private function consumeFixed(TokenKind $kind, int $length): array
    {
        return [$kind, $this->input->consume($length)];
    }
}
