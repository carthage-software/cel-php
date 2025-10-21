<?php

declare(strict_types=1);

namespace Cel\Parser;

use Cel\Exception\InternalException;
use Cel\Input\Input;
use Cel\Input\InputInterface;
use Cel\Lexer\Lexer;
use Cel\Parser\Exception\UnexpectedEndOfFileException;
use Cel\Parser\Exception\UnexpectedTokenException;
use Cel\Syntax\Expression;

/**
 * @require-implements ParserInterface
 */
trait ParserConvenienceMethodsTrait
{
    /**
     * Parses the string and produces a syntax tree.
     *
     * @param string $string The string to parse.
     *
     * @return Expression The root node of the resulting expression tree.
     *
     * @throws InternalException If an internal error occurs during parsing.
     * @throws UnexpectedEndOfFileException If the end of the file is reached unexpectedly.
     * @throws UnexpectedTokenException If an unexpected token is encountered.
     */
    public function parseString(string $string): Expression
    {
        return $this->parse(new Input($string));
    }

    /**
     * Parses the input and produces a syntax tree.
     *
     * @param InputInterface $input The input to parse.
     *
     * @return Expression The root node of the resulting expression tree.
     *
     * @throws UnexpectedEndOfFileException If the end of the file is reached unexpectedly.
     * @throws UnexpectedTokenException If an unexpected token is encountered.
     * @throws InternalException If internal parsing operations fail.
     */
    public function parse(InputInterface $input): Expression
    {
        return $this->construct(new Lexer($input));
    }
}
