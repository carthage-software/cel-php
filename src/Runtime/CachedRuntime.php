<?php

declare(strict_types=1);

namespace Cel\Runtime;

use Cel\Exception\EvaluationException;
use Cel\Exception\InternalException;
use Cel\Exception\MisconfigurationException;
use Cel\Extension\ExtensionInterface;
use Cel\Syntax\Expression;
use Override;
use Psr\SimpleCache\CacheInterface;
use Psr\SimpleCache\InvalidArgumentException;
use Throwable;

use function hash;
use function serialize;

/**
 * A runtime decorator that caches evaluation results for idempotent expressions.
 *
 * This decorator wraps a RuntimeInterface and caches the results of evaluating
 * expressions that are idempotent (produce the same result regardless of environment).
 *
 * Only idempotent expressions are cached to ensure correctness.
 */
final class CachedRuntime implements RuntimeInterface
{
    /**
     * Cache key prefix for runtime evaluation results.
     */
    private const string CACHE_KEY_PREFIX = 'cel_run_';

    /**
     * @param RuntimeInterface $runtime The wrapped runtime
     * @param CacheInterface $cache The cache implementation
     * @param int|null $cacheTtl Cache TTL in seconds (default: 3600). Use null for no expiration.
     */
    public function __construct(
        private readonly RuntimeInterface $runtime,
        private readonly CacheInterface $cache,
        private readonly null|int $cacheTtl = 3600,
    ) {}

    /**
     * @throws MisconfigurationException Always throws as CachedRuntime requires a cache implementation.
     */
    #[Override]
    public static function default(): static
    {
        throw MisconfigurationException::forMessage(
            'CachedRuntime cannot be constructed using default() - requires cache implementation.',
        );
    }

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
     * @throws InternalException if cache operations fail.
     */
    #[Override]
    public function run(Expression $expression, array $context = []): RuntimeReceipt
    {
        try {
            // Generate cache key from serialized expression
            $cacheKey = self::CACHE_KEY_PREFIX . hash('xxh128', serialize($expression) . serialize($context));
        } catch (Throwable $e) {
            // e.g: context contains non-serializable values
            throw InternalException::forMessage('Failed to generate cache key for expression and context.', $e);
        }

        try {
            /** @var RuntimeReceipt|null $cached */
            $cached = $this->cache->get($cacheKey);
        } catch (InvalidArgumentException $e) {
            throw InternalException::forMessage('Cache get operation failed', $e);
        }

        if ($cached instanceof RuntimeReceipt) {
            return $cached;
        }

        $receipt = $this->runtime->run($expression, $context);

        // Only cache if the expression is idempotent
        if ($receipt->idempotent) {
            try {
                $this->cache->set($cacheKey, $receipt, $this->cacheTtl);
            } catch (InvalidArgumentException $e) {
                throw InternalException::forMessage('Cache set operation failed', $e);
            }
        }

        return $receipt;
    }
}
