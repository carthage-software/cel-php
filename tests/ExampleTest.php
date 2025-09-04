<?php

declare(strict_types=1);

namespace Cel\Tests;

use Cel\Example;
use PHPUnit\Framework\Attributes\CoversMethod;
use PHPUnit\Framework\TestCase;

#[CoversMethod(Example::class, 'greet')]
final class ExampleTest extends TestCase
{
    public function testExample(): void
    {
        $example = new Example();

        static::assertSame('Hello, world!', $example->greet('World'));
    }
}
