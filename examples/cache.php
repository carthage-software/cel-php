<?php

declare(strict_types=1);

/**
 * Benchmark script to compare CEL performance with and without caching.
 *
 * Usage:
 *   php examples/benchmark_cache.php
 *
 * This script helps you determine if caching will benefit your use case.
 */

require_once __DIR__ . '/../vendor/autoload.php';

use Cel\CommonExpressionLanguage;
use Cel\Parser\CachedParser;
use Cel\Parser\Parser;
use Cel\Runtime\CachedRuntime;
use Cel\Runtime\Runtime;
use Psl\DateTime\Duration;
use Psl\DateTime\Timestamp;
use Psl\Io;
use Psl\Str;
use Symfony\Component\Cache\Adapter\ArrayAdapter;
use Symfony\Component\Cache\Psr16Cache;

// Sample data
$users = [
    ['id' => 1, 'name' => 'Alice', 'age' => 30, 'status' => 'active', 'email' => 'alice@example.com'],
    ['id' => 2, 'name' => 'Bob', 'age' => 25, 'status' => 'active', 'email' => 'bob@example.com'],
    ['id' => 3, 'name' => 'Charlie', 'age' => 35, 'status' => 'inactive', 'email' => 'charlie@example.com'],
    ['id' => 4, 'name' => 'Diana', 'age' => 28, 'status' => 'active', 'email' => 'diana@example.com'],
    ['id' => 5, 'name' => 'Eve', 'age' => 22, 'status' => 'active', 'email' => 'eve@example.com'],
];

// Test expressions of varying complexity
$expressions = [
    'simple' => [
        'expr' => 'x + y',
        'vars' => ['x' => 10, 'y' => 20],
    ],
    'medium' => [
        'expr' => 'user.age >= minAge && user.status == "active"',
        'vars' => ['user' => $users[0], 'minAge' => 18],
    ],
    'complex' => [
        'expr' => <<<CEL
            users.filter(u, u.age >= minAge && u.status == "active")
                 .map(u, {
                     "name": u.name,
                     "email": u.email,
                     "ageGroup": u.age < 25 ? "young" : (u.age < 30 ? "medium" : "senior")
                 })
                 .size() > 0
            CEL,
        'vars' => ['users' => $users, 'minAge' => 18],
    ],
];

$iterations = 1000;
$cache = new Psr16Cache(new ArrayAdapter());

// Create CEL instances
$celNoCache = CommonExpressionLanguage::default();
$celWithCache = new CommonExpressionLanguage(
    parser: new CachedParser(Parser::default(), $cache),
    runtime: new CachedRuntime(Runtime::default(), $cache),
);

Io\write_error_line('CEL Caching Benchmark');
Io\write_error_line(Str\repeat('=', 80));
Io\write_error_line('');
Io\write_error_line("Running {$iterations} iterations per test...");
Io\write_error_line('');

foreach ($expressions as $name => $test) {
    Io\write_error_line(Str\uppercase($name) . ' EXPRESSION');
    Io\write_error_line(Str\repeat('-', 80));

    // Benchmark WITHOUT cache
    $start = Timestamp::monotonic();
    for ($i = 0; $i < $iterations; $i++) {
        $expression = $celNoCache->parseString($test['expr']);
        $expression = $celNoCache->optimize($expression);
        $celNoCache->run($expression, $test['vars']);
    }
    $withoutCache = Timestamp::monotonic()->since($start);

    // Clear cache for fair comparison
    $cache->clear();

    // Benchmark WITH cache
    $start = Timestamp::monotonic();
    for ($i = 0; $i < $iterations; $i++) {
        $expression = $celWithCache->parseString($test['expr']);
        $expression = $celWithCache->optimize($expression);
        $celWithCache->run($expression, $test['vars']);
    }
    $withCache = Timestamp::monotonic()->since($start);

    // Calculate metrics
    $avgWithout = Duration::microseconds((int) ($withoutCache->getTotalMicroseconds() / $iterations));
    $avgWith = Duration::microseconds((int) ($withCache->getTotalMicroseconds() / $iterations));
    $speedup = $withoutCache->getTotalMicroseconds() / $withCache->getTotalMicroseconds();

    Io\write_error_line('Without cache: %s (%s / iteration)', $withoutCache->toString(4), $avgWithout->toString(6));
    Io\write_error_line('With cache:    %s (%s / iteration)', $withCache->toString(4), $avgWith->toString(6));

    if ($speedup > 1.0) {
        Io\write_error_line('✅ Speedup:     %.2fx faster with cache', $speedup);
    } elseif ($speedup < 1.0) {
        Io\write_error_line('❌ Slowdown:    %.2fx SLOWER with cache', 1 / $speedup);
        Io\write_error_line("   Recommendation: Don't use cache for this expression");
    } else {
        Io\write_error_line('⚠️  No difference');
    }

    Io\write_error_line('');
}

Io\write_error_line(Str\repeat('=', 80));
Io\write_error_line('IMPORTANT NOTES:');
Io\write_error_line(Str\repeat('=', 80));
Io\write_error_line('1. This benchmark uses ArrayAdapter (in-memory cache)');
Io\write_error_line('2. Real-world caches (filesystem, Redis) may be slower');
Io\write_error_line('3. APCu cache would be much faster than filesystem/network caches');
Io\write_error_line('4. Always benchmark with YOUR expressions and YOUR cache backend');
Io\write_error_line('5. CEL is fast - caching may not help for simple expressions');
Io\write_error_line('');

Io\write_error_line('Note: This benchmark uses the Decorator pattern:');
Io\write_error_line('  - CachedParser wraps Parser to cache parsed expressions');
Io\write_error_line('  - CachedRuntime wraps Runtime to cache idempotent results');
Io\write_error_line('  - You can use either or both depending on your needs');
Io\write_error_line('');
