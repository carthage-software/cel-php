<?php

declare(strict_types=1);

namespace Cel\Token;

use Cel\Span\Span;

/**
 * Represents a single token produced by the lexer.
 *
 * A token is the smallest meaningful unit of a program and consists of its kind (e.g., Identifier, Operator),
 * its value (the raw text), and its location in the source code (the span).
 */
final readonly class Token
{
    /**
     * @param Span $span The location of the token in the source code.
     * @param TokenKind $kind The category or type of the token.
     * @param string $value The raw string value of the token from the source code.
     */
    public function __construct(
        public Span $span,
        public TokenKind $kind,
        public string $value,
    ) {}
}
