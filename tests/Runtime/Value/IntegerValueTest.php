<?php

declare(strict_types=1);

namespace Cel\Tests\Runtime\Value;

use Cel\Runtime\Value\IntegerValue;
use PHPUnit\Framework\TestCase;

final class IntegerValueTest extends TestCase
{
    public function testValue(): void
    {
        $value = new IntegerValue(123);
        static::assertSame(123, $value->getNativeValue());
        static::assertSame('int', $value->getType());
    }
}
