<?php

declare(strict_types=1);

namespace Cel;

use Cel\Exception\EvaluationException;
use Cel\Extension\ExtensionInterface;
use Cel\Input\InputInterface;
use Cel\Lexer\LexerInterface;
use Cel\Optimizer\Optimization;
use Cel\Optimizer\Optimizer;
use Cel\Optimizer\OptimizerInterface;
use Cel\Parser\CachedParser;
use Cel\Parser\OptimizedParser;
use Cel\Parser\ParserInterface;
use Cel\Runtime\CachedRuntime;
use Cel\Runtime\Runtime;
use Cel\Runtime\RuntimeInterface;
use Cel\Runtime\RuntimeReceipt;
use Cel\Syntax\Expression;
use Override;
use Psr\SimpleCache\CacheInterface;

/**
 * Common Expression Language (CEL) implementation.
 *
 * This class provides a complete implementation of the CEL specification,
 * combining parsing, optimization, and runtime evaluation capabilities.
 *
 * It allows for creating CEL instances with default components or with
 * caching enabled for improved performance.
 *
 * @inheritors Cel
 */
readonly class CommonExpressionLanguage implements ParserInterface, OptimizerInterface, RuntimeInterface
{
    public function __construct(
        private ParserInterface $parser = new OptimizedParser(),
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
            parser: OptimizedParser::default(),
            optimizer: Optimizer::default(),
            runtime: Runtime::default(),
        );
    }

    /**
     * Creates a CEL instance with caching enabled using decorators.
     *
     * This method creates a CommonExpressionLanguage instance with both
     * CachedParser and CachedRuntime decorators wrapping the default
     * parser and runtime implementations.
     *
     * @param CacheInterface $cache The cache implementation to use
     * @param int|null $cacheTtl Cache TTL in seconds (default: 3600). Use null for no expiration.
     *
     * @return static A CEL instance with caching enabled
     */
    public static function cached(CacheInterface $cache, null|int $cacheTtl = 3600): static
    {
        return new self(
            parser: new CachedParser(OptimizedParser::default(), $cache, $cacheTtl),
            optimizer: Optimizer::default(),
            runtime: new CachedRuntime(Runtime::default(), $cache, $cacheTtl),
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
     * Evaluates the given expression with the provided context.
     *
     * The runtime maintains an internal environment with value resolvers from registered extensions.
     * For each run, it forks this environment and populates it with the provided context variables.
     *
     * @param Expression $expression The expression to evaluate.
     * @param array<string, mixed> $context Associative array of variable names to values for this execution.
     *
     * @return RuntimeReceipt The result of the evaluation, including the value and any relevant metadata.
     *
     * @throws EvaluationException on runtime errors.
     */
    #[Override]
    public function run(Expression $expression, array $context = []): RuntimeReceipt
    {
        return $this->runtime->run($expression, $context);
    }
}
