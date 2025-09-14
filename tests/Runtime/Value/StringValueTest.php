<?php

declare(strict_types=1);

namespace Cel\Tests\Runtime\Value;

use Cel\Runtime\Value\StringValue;
use PHPUnit\Framework\TestCase;

final class StringValueTest extends TestCase
{
    public function testValue(): void
    {
        $value = new StringValue('hello');
        static::assertSame('hello', $value->getNativeValue());
        static::assertSame('string', $value->getType());
    }
}
