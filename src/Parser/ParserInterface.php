<?php

declare(strict_types=1);

namespace Cel\Parser;

use Cel\Input\InputInterface;
use Cel\Lexer\LexerInterface;
use Cel\Parser\Exception\UnexpectedEndOfFileException;
use Cel\Parser\Exception\UnexpectedTokenException;
use Cel\Syntax\Expression;
use Psl\Default\DefaultInterface;

/**
 * Defines the contract for a parser, which consumes a stream of tokens from a lexer
 * and produces a syntax tree.
 */
interface ParserInterface extends DefaultInterface
{
    /**
     * Parses the string and produces a syntax tree.
     *
     * @param string $string The string to parse.
     *
     * @return Expression The root node of the resulting expression tree.
     *
     * @throws UnexpectedEndOfFileException If the end of the file is reached unexpectedly.
     * @throws UnexpectedTokenException If an unexpected token is encountered.
     */
    public function parseString(string $string): Expression;

    /**
     * Parses the input and produces a syntax tree.
     *
     * @param InputInterface $input The input to parse.
     *
     * @return Expression The root node of the resulting expression tree.
     *
     * @throws UnexpectedEndOfFileException If the end of the file is reached unexpectedly.
     * @throws UnexpectedTokenException If an unexpected token is encountered.
     */
    public function parse(InputInterface $input): Expression;

    /**
     * Constructs a syntax tree from the provided lexer.
     *
     * @param LexerInterface $lexer The lexer providing the tokens.
     *
     * @return Expression The root node of the resulting expression tree.
     *
     * @throws UnexpectedEndOfFileException If the end of the file is reached unexpectedly.
     * @throws UnexpectedTokenException If an unexpected token is encountered.
     */
    public function construct(LexerInterface $lexer): Expression;
}
