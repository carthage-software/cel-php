<?php

namespace Cel\Parser;

use Cel\Input\InputInterface;
use Cel\Lexer\LexerInterface;
use Cel\Syntax\Expression;

/**
 * Defines the contract for a parser, which consumes a stream of tokens from a lexer
 * and produces a syntax tree.
 */
interface ParserInterface
{
    /**
     * Parses the string and produces a syntax tree.
     *
     * @param string $string The string to parse.
     *
     * @return Expression The root node of the resulting expression tree.
     */
    public function parseString(string $string): Expression;

    /**
     * Parses the input and produces a syntax tree.
     *
     * @param InputInterface $input The input to parse.
     *
     * @return Expression The root node of the resulting expression tree.
     */
    public function parse(InputInterface $input): Expression;

    /**
     * Constructs a syntax tree from the provided lexer.
     *
     * @param LexerInterface $lexer The lexer providing the tokens.
     *
     * @return Expression The root node of the resulting expression tree.
     */
    public function construct(LexerInterface $lexer): Expression;
}
