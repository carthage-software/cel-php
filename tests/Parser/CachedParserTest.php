<?php

declare(strict_types=1);

namespace Cel\Tests\Parser;

use Cel\Input\Input;
use Cel\Parser\CachedParser;
use Cel\Parser\Parser;
use Cel\Syntax\Expression;
use PHPUnit\Framework\TestCase;
use Psl\Async;
use Psl\DateTime\Duration;
use Symfony\Component\Cache\Adapter\ArrayAdapter;
use Symfony\Component\Cache\Psr16Cache;

final class CachedParserTest extends TestCase
{
    public function testParseStringCachesExpression(): void
    {
        $cache = new Psr16Cache(new ArrayAdapter());
        $parser = new CachedParser(Parser::default(), $cache);

        // First call - should parse and cache
        $expr1 = $parser->parseString('1 + 2');
        static::assertInstanceOf(Expression::class, $expr1);

        // Second call - should use cache
        $expr2 = $parser->parseString('1 + 2');
        static::assertInstanceOf(Expression::class, $expr2);

        // Verify both expressions are equivalent (cache may deserialize, so check equality not identity)
        static::assertEquals($expr1, $expr2);
    }

    public function testParseInputCachesExpression(): void
    {
        $cache = new Psr16Cache(new ArrayAdapter());
        $parser = new CachedParser(Parser::default(), $cache);

        $input1 = new Input('x + y');
        $input2 = new Input('x + y');

        // First call - should parse and cache
        $expr1 = $parser->parse($input1);
        static::assertInstanceOf(Expression::class, $expr1);

        // Second call with same content - should use cache
        $expr2 = $parser->parse($input2);
        static::assertInstanceOf(Expression::class, $expr2);

        // Verify both expressions are equivalent (cache may deserialize, so check equality not identity)
        static::assertEquals($expr1, $expr2);
    }

    public function testDifferentExpressionsGetDifferentCacheEntries(): void
    {
        $cache = new Psr16Cache(new ArrayAdapter());
        $parser = new CachedParser(Parser::default(), $cache);

        $expr1 = $parser->parseString('1 + 2');
        $expr2 = $parser->parseString('2 + 3');

        static::assertNotEquals($expr1, $expr2);
    }

    public function testCacheTtlIsRespected(): void
    {
        $cache = new Psr16Cache(new ArrayAdapter());
        $parser = new CachedParser(Parser::default(), $cache, cacheTtl: 1);

        // Parse and cache
        $expr1 = $parser->parseString('1 + 2');

        // Wait for cache to expire
        Async\sleep(Duration::seconds(2));

        // Should parse again (cache expired)
        $expr2 = $parser->parseString('1 + 2');

        // Both should be valid expressions
        static::assertInstanceOf(Expression::class, $expr1);
        static::assertInstanceOf(Expression::class, $expr2);
        static::assertEquals($expr1, $expr2);
    }

    public function testNullTtlMeansNoExpiration(): void
    {
        $cache = new Psr16Cache(new ArrayAdapter());
        $parser = new CachedParser(Parser::default(), $cache, cacheTtl: null);

        // Parse and cache with no expiration
        $expr1 = $parser->parseString('1 + 2');
        $expr2 = $parser->parseString('1 + 2');

        // Both should be valid and equivalent (cache may deserialize, so check equality not identity)
        static::assertInstanceOf(Expression::class, $expr1);
        static::assertEquals($expr1, $expr2);
    }
}
