<?php

declare(strict_types=1);

namespace Cel;

use Cel\Input\InputInterface;
use Cel\Lexer\LexerInterface;
use Cel\Optimizer\Optimization;
use Cel\Optimizer\Optimizer;
use Cel\Optimizer\OptimizerInterface;
use Cel\Parser\Parser;
use Cel\Parser\ParserInterface;
use Cel\Runtime\Environment\EnvironmentInterface;
use Cel\Runtime\Extension\ExtensionInterface;
use Cel\Runtime\Runtime;
use Cel\Runtime\RuntimeInterface;
use Cel\Runtime\RuntimeReceipt;
use Cel\Syntax\Expression;
use Override;

final readonly class CommonExpressionLanguage implements ParserInterface, OptimizerInterface, RuntimeInterface
{
    public function __construct(
        private ParserInterface $parser = new Parser(),
        private OptimizerInterface $optimizer = new Optimizer(),
        private RuntimeInterface $runtime = new Runtime(),
    ) {}

    /**
     * @inheritDoc
     */
    #[Override]
    public static function default(): static
    {
        return new self(
            parser: Parser::default(),
            optimizer: Optimizer::default(),
            runtime: Runtime::default(),
        );
    }

    /**
     * @inheritDoc
     */
    #[Override]
    public function parseString(string $string): Expression
    {
        return $this->parser->parseString($string);
    }

    /**
     * @inheritDoc
     */
    #[Override]
    public function parse(InputInterface $input): Expression
    {
        return $this->parser->parse($input);
    }

    /**
     * @inheritDoc
     */
    #[Override]
    public function construct(LexerInterface $lexer): Expression
    {
        return $this->parser->construct($lexer);
    }

    /**
     * @inheritDoc
     */
    #[Override]
    public function addOptimization(Optimization\OptimizationInterface $optimization): void
    {
        $this->optimizer->addOptimization($optimization);
    }

    /**
     * @inheritDoc
     */
    #[Override]
    public function optimize(Expression $expression): Expression
    {
        return $this->optimizer->optimize($expression);
    }

    /**
     * @inheritDoc
     */
    #[Override]
    public function register(ExtensionInterface $extension): void
    {
        $this->runtime->register($extension);
    }

    /**
     * @inheritDoc
     */
    #[Override]
    public function run(Expression $expression, EnvironmentInterface $environment): RuntimeReceipt
    {
        return $this->runtime->run($expression, $environment);
    }
}
