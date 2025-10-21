# Caching in CEL-PHP

CEL-PHP provides built-in caching support to improve performance when evaluating the same expressions multiple times.

## When to Use Caching

Caching is beneficial for:

1. **Repeated Expression Parsing** - When you parse the same expression string multiple times
2. **Idempotent Evaluations** - When expressions produce the same result regardless of context
3. **High-Volume Evaluations** - When processing many requests with similar expressions

For simple expressions or one-time evaluations, caching may add unnecessary overhead.

## PSR-16 Cache Support

CEL-PHP uses PSR-16 Simple Cache interfaces. You can use any PSR-16 compatible cache implementation.

Install a cache library (example using Symfony Cache):

```bash
composer require symfony/cache
```

## Using Cached CEL

The easiest way to enable caching:

```php
<?php

use Cel\CommonExpressionLanguage;
use Symfony\Component\Cache\Adapter\ApcuAdapter;
use Symfony\Component\Cache\Psr16Cache;

// Create any PSR-16 cache instance
$cache = new Psr16Cache(new ApcuAdapter());

// Create CEL with caching enabled
$cel = CommonExpressionLanguage::cached(
    cache: $cache,
    cacheTtl: 3600, // Optional: cache TTL in seconds (default: 3600)
);

// Use it like normal - parsing and evaluation are now cached
$expression = $cel->parseString('user.age >= 18 && user.status == "active"');
$receipt = $cel->run($expression, ['user' => ['age' => 25, 'status' => 'active']]);
```

## Cache Parser Only

Cache only the parsing step:

```php
<?php

use Cel\Parser\CachedParser;
use Cel\Parser\Parser;

$parser = new CachedParser(
    parser: Parser::default(),
    cache: $cache,
    cacheTtl: 3600,
);

// First parse: parsed and cached
// Subsequent parses: retrieved from cache
$expression = $parser->parseString('price * quantity > 100');
```

## Cache Runtime Only

Cache only evaluation results for idempotent expressions:

```php
<?php

use Cel\Runtime\CachedRuntime;
use Cel\Runtime\Runtime;

$runtime = new CachedRuntime(
    runtime: Runtime::default(),
    cache: $cache,
    cacheTtl: 3600,
);

// First evaluation: evaluated and cached
// Subsequent evaluations: retrieved from cache
$receipt = $runtime->run($expression);
```

**Note**: `CachedRuntime` only caches idempotent expressions. Expressions with variables are not cached.

## Manual Configuration

For fine-grained control:

```php
<?php

use Cel\CommonExpressionLanguage;
use Cel\Optimizer\Optimizer;
use Cel\Parser\CachedParser;
use Cel\Parser\OptimizedParser;
use Cel\Runtime\CachedRuntime;
use Cel\Runtime\Runtime;

$cel = new CommonExpressionLanguage(
    parser: new CachedParser(
        parser: OptimizedParser::default(),
        cache: $parserCache,
        cacheTtl: 7200,
    ),
    optimizer: Optimizer::default(),
    runtime: new CachedRuntime(
        runtime: Runtime::default(),
        cache: $runtimeCache,
        cacheTtl: 3600,
    ),
);
```

## Cache TTL

Control cache lifetime:

```php
// 1 hour (default)
$cel = CommonExpressionLanguage::cached($cache, cacheTtl: 3600);

// 24 hours
$cel = CommonExpressionLanguage::cached($cache, cacheTtl: 86400);

// No expiration
$cel = CommonExpressionLanguage::cached($cache, cacheTtl: null);
```

## Cache Invalidation

Clear the cache when needed:

```php
// Clear all cache
$cache->clear();

// Or use versioning with your cache adapter
$cache = new Psr16Cache(new ApcuAdapter(
    namespace: 'cel',
    version: '2.0', // Increment to invalidate all cache
));
```

## Complete Example

```php
<?php

use Cel\CommonExpressionLanguage;
use Symfony\Component\Cache\Adapter\ApcuAdapter;
use Symfony\Component\Cache\Psr16Cache;

$cache = new Psr16Cache(new ApcuAdapter());
$cel = CommonExpressionLanguage::cached($cache);

// Register extensions if needed
$cel->register(new YourCustomExtension());

// Parse once, use many times
$expression = $cel->parseString('user.age >= 18 && user.permissions.contains("admin")');
$expression = $cel->optimize($expression);

// Evaluate with different contexts
$users = [
    ['age' => 25, 'permissions' => ['admin', 'write']],
    ['age' => 30, 'permissions' => ['read']],
    ['age' => 17, 'permissions' => ['admin']],
];

foreach ($users as $user) {
    $receipt = $cel->run($expression, ['user' => $user]);
    echo $receipt->result->getRawValue() ? 'Access granted' : 'Access denied';
    echo "\n";
}
```

## Next Steps

- Learn about [Custom Functions](./custom-functions.md)
- Learn about [Custom Operators](./custom-operators.md)
- Explore [Getting Started](./getting-started.md)
