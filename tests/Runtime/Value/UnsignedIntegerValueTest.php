<?php

declare(strict_types=1);

namespace Cel\Tests\Runtime\Value;

use Cel\Value\UnsignedIntegerValue;
use PHPUnit\Framework\TestCase;

final class UnsignedIntegerValueTest extends TestCase
{
    public function testValue(): void
    {
        $value = new UnsignedIntegerValue(123);
        static::assertSame(123, $value->getRawValue());
        static::assertSame('uint', $value->getType());
    }
}
