<?php

declare(strict_types=1);

namespace Cel\Parser;

use Cel\Exception\InternalException;
use Cel\Exception\MisconfigurationException;
use Cel\Input\InputInterface;
use Cel\Lexer\LexerInterface;
use Cel\Parser\Exception\UnexpectedEndOfFileException;
use Cel\Parser\Exception\UnexpectedTokenException;
use Cel\Syntax\Expression;
use Override;
use Psr\SimpleCache\CacheInterface;
use Psr\SimpleCache\InvalidArgumentException;

use function hash;

/**
 * A parser decorator that caches parsed expressions.
 *
 * This decorator wraps a ParserInterface and caches parsed expressions
 * to avoid re-parsing the same input multiple times.
 */
final readonly class CachedParser implements ParserInterface
{
    /**
     * Cache key prefix for parsed expressions.
     */
    private const string CACHE_KEY_PREFIX = 'cel_parse_';

    /**
     * @param ParserInterface $parser The wrapped parser
     * @param CacheInterface $cache The cache implementation
     * @param int|null $cacheTtl Cache TTL in seconds (default: 3600). Use null for no expiration.
     */
    public function __construct(
        private ParserInterface $parser,
        private CacheInterface $cache,
        private null|int $cacheTtl = 3600,
    ) {}

    /**
     * @throws MisconfigurationException Always throws as CachedParser requires a cache implementation.
     */
    #[Override]
    public static function default(): static
    {
        throw MisconfigurationException::forMessage(
            'CachedParser cannot be constructed using default() - requires cache implementation.',
        );
    }

    /**
     * @throws InternalException
     * @throws UnexpectedEndOfFileException
     * @throws UnexpectedTokenException
     */
    #[Override]
    public function parseString(string $string): Expression
    {
        $cacheKey = self::CACHE_KEY_PREFIX . hash('xxh128', $string);

        try {
            /** @var Expression|null $cached */
            $cached = $this->cache->get($cacheKey);
        } catch (InvalidArgumentException $e) {
            throw InternalException::forMessage('Cache operation failed: ' . $e->getMessage());
        }

        if ($cached instanceof Expression) {
            return $cached;
        }

        $expression = $this->parser->parseString($string);

        try {
            $this->cache->set($cacheKey, $expression, $this->cacheTtl);
        } catch (InvalidArgumentException $e) {
            throw InternalException::forMessage('Cache operation failed: ' . $e->getMessage());
        }

        return $expression;
    }

    /**
     * @throws InternalException
     * @throws UnexpectedEndOfFileException
     * @throws UnexpectedTokenException
     */
    #[Override]
    public function parse(InputInterface $input): Expression
    {
        $cacheKey = self::CACHE_KEY_PREFIX . $input->getHash();

        try {
            /** @var Expression|null $cached */
            $cached = $this->cache->get($cacheKey);
        } catch (InvalidArgumentException $e) {
            throw InternalException::forMessage('Cache operation failed: ' . $e->getMessage());
        }

        if ($cached instanceof Expression) {
            return $cached;
        }

        $expression = $this->parser->parse($input);

        try {
            $this->cache->set($cacheKey, $expression, $this->cacheTtl);
        } catch (InvalidArgumentException $e) {
            throw InternalException::forMessage('Cache operation failed: ' . $e->getMessage());
        }

        return $expression;
    }

    /**
     * @throws UnexpectedEndOfFileException
     * @throws UnexpectedTokenException
     */
    #[Override]
    public function construct(LexerInterface $lexer): Expression
    {
        // Cannot cache when using lexer directly as we don't have the source
        return $this->parser->construct($lexer);
    }
}
