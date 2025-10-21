<?php

declare(strict_types=1);

namespace Cel\Tests\Runtime\Environment;

use Cel\Environment\Environment;
use Cel\Value\IntegerValue;
use Cel\Value\Resolver\ValueResolverInterface;
use Cel\Value\StringValue;
use Cel\Value\Value;
use Override;
use PHPUnit\Framework\TestCase;

use function is_int;

final class EnvironmentTest extends TestCase
{
    public function testVariables(): void
    {
        $env = new Environment();
        static::assertFalse($env->hasVariable('x'));
        static::assertNull($env->getVariable('x'));

        $value = new IntegerValue(123);
        $env->addVariable('x', $value);

        static::assertTrue($env->hasVariable('x'));
        static::assertSame($value, $env->getVariable('x'));
    }

    public function testDefaultConstructor(): void
    {
        $env = Environment::default();

        static::assertInstanceOf(Environment::class, $env);
        static::assertFalse($env->hasVariable('test'));
    }

    public function testFromArray(): void
    {
        $env = Environment::fromArray([
            'x' => 1,
            'y' => 'hello',
            'z' => true,
        ]);

        static::assertTrue($env->hasVariable('x'));
        static::assertTrue($env->hasVariable('y'));
        static::assertTrue($env->hasVariable('z'));

        $x = $env->getVariable('x');
        static::assertInstanceOf(IntegerValue::class, $x);
        static::assertSame(1, $x->value);

        $y = $env->getVariable('y');
        static::assertInstanceOf(StringValue::class, $y);
        static::assertSame('hello', $y->value);
    }

    public function testAddRaw(): void
    {
        $env = new Environment();

        $env->addRaw('x', 42);

        static::assertTrue($env->hasVariable('x'));
        $value = $env->getVariable('x');
        static::assertInstanceOf(IntegerValue::class, $value);
        static::assertSame(42, $value->value);
    }

    public function testAddRawWithCustomResolver(): void
    {
        $env = new Environment();

        // Create a custom resolver that wraps integers in StringValue with the integer as string
        $customResolver = new class implements ValueResolverInterface {
            #[Override]
            public function canResolve(mixed $value): bool
            {
                return is_int($value);
            }

            #[Override]
            public function resolve(mixed $value): Value
            {
                return new StringValue((string) $value);
            }
        };

        $env->registerValueResolver($customResolver);
        $env->addRaw('x', 42);

        static::assertTrue($env->hasVariable('x'));
        $value = $env->getVariable('x');
        static::assertInstanceOf(StringValue::class, $value);
        static::assertSame('42', $value->value);
    }

    public function testRegisterValueResolver(): void
    {
        $env = new Environment();

        $resolver = $this->createMock(ValueResolverInterface::class);
        $resolver->method('canResolve')->willReturn(false);

        // Should not throw
        $env->registerValueResolver($resolver);

        static::assertTrue(true);
    }

    public function testResolverPriority(): void
    {
        $env = new Environment();

        // First resolver returns IntegerValue
        $resolver1 = new class implements ValueResolverInterface {
            #[Override]
            public function canResolve(mixed $value): bool
            {
                return $value === 'test';
            }

            #[Override]
            public function resolve(mixed $value): Value
            {
                return new IntegerValue(1);
            }
        };

        // Second resolver returns StringValue
        $resolver2 = new class implements ValueResolverInterface {
            #[Override]
            public function canResolve(mixed $value): bool
            {
                return $value === 'test';
            }

            #[Override]
            public function resolve(mixed $value): Value
            {
                return new StringValue('2');
            }
        };

        $env->registerValueResolver($resolver1);
        $env->registerValueResolver($resolver2);

        // Most recently registered resolver should be used first
        $env->addRaw('x', 'test');

        $value = $env->getVariable('x');
        static::assertInstanceOf(StringValue::class, $value);
        static::assertSame('2', $value->value);
    }

    public function testFork(): void
    {
        $env = new Environment();
        $env->addVariable('x', new IntegerValue(42));

        $forked = $env->fork();

        // Forked environment should have the same variables
        static::assertTrue($forked->hasVariable('x'));
        static::assertSame(42, $forked->getVariable('x')?->getRawValue());

        // Changes to forked environment should not affect original
        $forked->addVariable('y', new IntegerValue(100));

        static::assertTrue($forked->hasVariable('y'));
        static::assertFalse($env->hasVariable('y'));
    }

    public function testForkWithCustomResolvers(): void
    {
        $env = new Environment();

        // Add a custom resolver
        $customResolver = new class implements ValueResolverInterface {
            #[Override]
            public function canResolve(mixed $value): bool
            {
                return $value === 'custom';
            }

            #[Override]
            public function resolve(mixed $value): Value
            {
                return new StringValue('resolved');
            }
        };

        $env->registerValueResolver($customResolver);

        // Fork should inherit custom resolvers
        $forked = $env->fork();
        $forked->addRaw('test', 'custom');

        $value = $forked->getVariable('test');
        static::assertInstanceOf(StringValue::class, $value);
        static::assertSame('resolved', $value->value);
    }

    public function testConstructorWithVariables(): void
    {
        $value = new IntegerValue(42);
        $env = new Environment(['x' => $value]);

        static::assertTrue($env->hasVariable('x'));
        static::assertSame($value, $env->getVariable('x'));
    }

    public function testConstructorWithValueResolvers(): void
    {
        $resolver = new class implements ValueResolverInterface {
            #[Override]
            public function canResolve(mixed $value): bool
            {
                return $value === 'magic';
            }

            #[Override]
            public function resolve(mixed $value): Value
            {
                return new StringValue('resolved');
            }
        };

        $env = new Environment([], [$resolver]);
        $env->addRaw('x', 'magic');

        $value = $env->getVariable('x');
        static::assertInstanceOf(StringValue::class, $value);
        static::assertSame('resolved', $value->value);
    }
}
