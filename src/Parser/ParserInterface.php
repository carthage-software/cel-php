<?php

namespace Cel\Parser;

use Cel\Lexer\LexerInterface;
use Cel\Syntax\Expression;

/**
 * Defines the contract for a parser, which consumes a stream of tokens from a lexer
 * and produces a syntax tree.
 */
interface ParserInterface
{
    /**
     * Parses the token stream from the given lexer into an expression tree.
     *
     * @param LexerInterface $lexer The lexer providing the stream of tokens.
     *
     * @return Expression The root node of the resulting expression tree.
     */
    public function parse(LexerInterface $lexer): Expression;
}
