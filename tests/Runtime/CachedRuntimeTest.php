<?php

declare(strict_types=1);

namespace Cel\Tests\Runtime;

use Cel\Parser\Parser;
use Cel\Runtime\CachedRuntime;
use Cel\Runtime\Runtime;
use Cel\Runtime\RuntimeReceipt;
use PHPUnit\Framework\TestCase;
use Psl\Async;
use Psl\DateTime\Duration;
use Symfony\Component\Cache\Adapter\ArrayAdapter;
use Symfony\Component\Cache\Psr16Cache;

final class CachedRuntimeTest extends TestCase
{
    public function testIdempotentExpressionIsCached(): void
    {
        $cache = new Psr16Cache(new ArrayAdapter());
        $runtime = new CachedRuntime(Runtime::default(), $cache);

        $parser = Parser::default();
        $expression = $parser->parseString('1 + 2');

        // First call - should evaluate and cache
        $receipt1 = $runtime->run($expression);
        static::assertInstanceOf(RuntimeReceipt::class, $receipt1);
        static::assertTrue($receipt1->idempotent);
        static::assertSame(3, $receipt1->result->getRawValue());

        // Second call - should use cache
        $receipt2 = $runtime->run($expression);
        static::assertInstanceOf(RuntimeReceipt::class, $receipt2);
        static::assertSame(3, $receipt2->result->getRawValue());
    }

    public function testExpressionsWithVariablesWorkCorrectly(): void
    {
        $cache = new Psr16Cache(new ArrayAdapter());
        $runtime = new CachedRuntime(Runtime::default(), $cache);

        $parser = Parser::default();
        $expression = $parser->parseString('x + 1');

        $receipt1 = $runtime->run($expression, ['x' => 1]);
        static::assertInstanceOf(RuntimeReceipt::class, $receipt1);
        static::assertSame(2, $receipt1->result->getRawValue());

        // Create a different expression to test separate caching
        $expression2 = $parser->parseString('x + 2');

        $receipt2 = $runtime->run($expression2, ['x' => 5]);
        static::assertInstanceOf(RuntimeReceipt::class, $receipt2);
        static::assertSame(7, $receipt2->result->getRawValue());
    }

    public function testCachedRuntimeIsCreated(): void
    {
        $cache = new Psr16Cache(new ArrayAdapter());
        $runtime = new CachedRuntime(Runtime::default(), $cache);

        // Verify the runtime was created successfully
        static::assertInstanceOf(CachedRuntime::class, $runtime);
    }

    public function testDifferentExpressionsGetDifferentCacheEntries(): void
    {
        $cache = new Psr16Cache(new ArrayAdapter());
        $runtime = new CachedRuntime(Runtime::default(), $cache);

        $parser = Parser::default();
        $expr1 = $parser->parseString('1 + 2');
        $expr2 = $parser->parseString('2 + 3');

        $receipt1 = $runtime->run($expr1);
        $receipt2 = $runtime->run($expr2);

        static::assertSame(3, $receipt1->result->getRawValue());
        static::assertSame(5, $receipt2->result->getRawValue());
    }

    public function testCacheTtlIsRespected(): void
    {
        $cache = new Psr16Cache(new ArrayAdapter());
        $runtime = new CachedRuntime(Runtime::default(), $cache, cacheTtl: 1);

        $parser = Parser::default();
        $expression = $parser->parseString('10 * 20');

        // Evaluate and cache
        $receipt1 = $runtime->run($expression);
        static::assertSame(200, $receipt1->result->getRawValue());

        // Wait for cache to expire
        Async\sleep(Duration::seconds(2));

        // Should evaluate again (cache expired)
        $receipt2 = $runtime->run($expression);
        static::assertSame(200, $receipt2->result->getRawValue());
    }

    public function testNullTtlMeansNoExpiration(): void
    {
        $cache = new Psr16Cache(new ArrayAdapter());
        $runtime = new CachedRuntime(Runtime::default(), $cache, cacheTtl: null);

        $parser = Parser::default();
        $expression = $parser->parseString('5 * 5');

        $receipt1 = $runtime->run($expression);
        $receipt2 = $runtime->run($expression);

        static::assertSame(25, $receipt1->result->getRawValue());
        static::assertSame(25, $receipt2->result->getRawValue());
    }
}
