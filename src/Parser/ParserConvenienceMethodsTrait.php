<?php

declare(strict_types=1);

namespace Cel\Parser;

use Cel\Input\Input;
use Cel\Input\InputInterface;
use Cel\Lexer\Lexer;
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
     */
    public function parse(InputInterface $input): Expression
    {
        return $this->construct(new Lexer($input));
    }
}
