<?php

declare(strict_types=1);

namespace Cel\Parser;

use Cel\Input\InputInterface;
use Cel\Lexer\LexerInterface;
use Cel\Optimizer\Optimizer;
use Cel\Optimizer\OptimizerInterface;
use Cel\Parser\Exception\UnexpectedEndOfFileException;
use Cel\Parser\Exception\UnexpectedTokenException;
use Cel\Runtime\Runtime;
use Cel\Runtime\RuntimeInterface;
use Cel\Syntax\Expression;
use Override;

/**
 * A parser decorator that automatically optimizes parsed expressions.
 *
 * This decorator wraps a ParserInterface and OptimizerInterface to
 * automatically optimize expressions after parsing them.
 *
 * @api
 */
final readonly class OptimizedParser implements ParserInterface
{
    private ParserInterface $parser;

    private OptimizerInterface $optimizer;

    /**
     * @param ParserInterface $parser The wrapped parser.
     * @param null|OptimizerInterface $optimizer The optimizer to apply to parsed expressions; when null,
     *                                           a default optimizer whose constant folding is bound to
     *                                           `$runtime` is used.
     * @param RuntimeInterface $runtime The runtime used by constant folding to honour registered extensions.
     */
    public function __construct(
        ParserInterface $parser = new Parser(),
        null|OptimizerInterface $optimizer = null,
        RuntimeInterface $runtime = new Runtime(),
    ) {
        $this->parser = $parser;
        $this->optimizer = $optimizer ?? new Optimizer($runtime);
    }

    #[Override]
    public static function default(): static
    {
        return new self();
    }

    /**
     * @throws UnexpectedEndOfFileException
     * @throws UnexpectedTokenException
     */
    #[Override]
    public function parseString(string $string): Expression
    {
        $expression = $this->parser->parseString($string);

        return $this->optimizer->optimize($expression);
    }

    /**
     * @throws UnexpectedEndOfFileException
     * @throws UnexpectedTokenException
     */
    #[Override]
    public function parse(InputInterface $input): Expression
    {
        $expression = $this->parser->parse($input);

        return $this->optimizer->optimize($expression);
    }

    /**
     * @throws UnexpectedEndOfFileException
     * @throws UnexpectedTokenException
     */
    #[Override]
    public function construct(LexerInterface $lexer): Expression
    {
        $expression = $this->parser->construct($lexer);

        return $this->optimizer->optimize($expression);
    }
}
