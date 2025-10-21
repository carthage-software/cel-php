<?php

declare(strict_types=1);

namespace Cel\Tests\Value\Resolver;

use Cel\Value\IntegerValue;
use Cel\Value\Resolver\DefaultValueResolver;
use Cel\Value\StringValue;
use PHPUnit\Framework\TestCase;

final class DefaultValueResolverTest extends TestCase
{
    public function testCanResolveAnything(): void
    {
        $resolver = new DefaultValueResolver();

        static::assertTrue($resolver->canResolve(42));
        static::assertTrue($resolver->canResolve('hello'));
        static::assertTrue($resolver->canResolve([]));
        static::assertTrue($resolver->canResolve(null));
        static::assertTrue($resolver->canResolve(new \stdClass()));
    }

    public function testResolveInteger(): void
    {
        $resolver = new DefaultValueResolver();

        $value = $resolver->resolve(42);

        static::assertInstanceOf(IntegerValue::class, $value);
        static::assertSame(42, $value->value);
    }

    public function testResolveString(): void
    {
        $resolver = new DefaultValueResolver();

        $value = $resolver->resolve('hello');

        static::assertInstanceOf(StringValue::class, $value);
        static::assertSame('hello', $value->value);
    }
}
