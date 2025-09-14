<?php

declare(strict_types=1);

namespace Cel\Tests\Runtime\Environment;

use Cel\Runtime\Environment\Environment;
use Cel\Runtime\Value\IntegerValue;
use PHPUnit\Framework\TestCase;

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
}
