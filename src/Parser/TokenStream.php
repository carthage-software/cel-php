<?php

declare(strict_types=1);

namespace Cel\Parser;

use Cel\Common\HasCursorInterface;
use Cel\Lexer\LexerInterface;
use Cel\Parser\Exception\UnexpectedEndOfFileException;
use Cel\Parser\Exception\UnexpectedTokenException;
use Cel\Token\Token;
use Cel\Token\TokenKind;
use Override;
use Psl\Iter;

use function array_shift;

/**
 * A buffered token stream that wraps a Lexer, providing lookahead
 * capabilities and automatically skipping trivia tokens (whitespace, comments).
 */
final class TokenStream implements HasCursorInterface
{
    private LexerInterface $lexer;

    /**
     * @var int<0, max> The current cursor position.
     */
    private int $cursor = 0;

    /**
     * @var list<Token> The lookahead buffer for non-trivia tokens.
     */
    private array $buffer = [];

    public function __construct(LexerInterface $lexer)
    {
        $this->lexer = $lexer;
    }

    /**
     * Returns the position at the *end* of the most recently consumed significant token.
     *
     * @return int<0, max> The current cursor position.
     */
    #[Override]
    public function cursorPosition(): int
    {
        return $this->cursor;
    }

    /**
     * Checks if there are any more significant (non-trivia) tokens in the stream.
     */
    #[Override]
    public function hasReachedEnd(): bool
    {
        $this->fillBuffer(1);

        return Iter\count($this->buffer) === 0;
    }

    /**
     * Consumes and returns the next significant token.
     *
     * @throws UnexpectedEndOfFileException If the end of the stream is reached.
     */
    public function consume(): Token
    {
        $this->fillBuffer(1);
        $token = array_shift($this->buffer);
        if (null === $token) {
            throw new UnexpectedEndOfFileException($this->cursorPosition(), []);
        }

        $this->cursor = $token->span->end;

        return $token;
    }

    /**
     * Consumes the next token *only if* it matches the expected kind.
     *
     * @throws UnexpectedTokenException If the next token does not match the expected kind.
     * @throws UnexpectedEndOfFileException If the end of the stream is reached.
     */
    public function eat(TokenKind $kind): Token
    {
        $token = $this->peek();
        if ($token->kind !== $kind) {
            throw new UnexpectedTokenException($token, [$kind]);
        }

        return $this->consume();
    }

    /**
     * Checks if the next significant token is of the expected kind.
     */
    public function isAt(TokenKind $kind): bool
    {
        $token = $this->lookahead(0);

        return null !== $token && $token->kind === $kind;
    }

    /**
     * Peeks at the next significant token without consuming it.
     *
     * @throws UnexpectedEndOfFileException If the end of the stream is reached.
     */
    public function peek(): Token
    {
        $token = $this->lookahead(0);
        if (null === $token) {
            throw new UnexpectedEndOfFileException($this->cursorPosition(), []);
        }

        return $token;
    }

    /**
     * Peeks at the nth (0-indexed) significant token ahead without consuming it.
     *
     * @param non-negative-int $n The number of tokens to look ahead (0 for the next token, 1 for the one after that, etc.).
     *
     * @return null|Token The token if it exists, or null if the end of the stream is reached.
     */
    public function lookahead(int $n): null|Token
    {
        $this->fillBuffer($n + 1);

        return $this->buffer[$n] ?? null;
    }

    /**
     * Ensures the lookahead buffer contains at least `n` significant items.
     */
    private function fillBuffer(int $n): void
    {
        while (Iter\count($this->buffer) < $n) {
            $token = $this->lexer->advance();
            if (null === $token) {
                // Lexer has reached the end.
                return;
            }

            if ($token->kind === TokenKind::Whitespace || $token->kind === TokenKind::Comment) {
                // Skip trivia tokens.
                continue;
            }

            $this->buffer[] = $token;
        }
    }
}
