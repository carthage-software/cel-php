<?php

declare(strict_types=1);

namespace Cel\Tests\Runtime\Value;

use Cel\Runtime\Value\NullValue;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\TestCase;

#[CoversClass(NullValue::class)]
final class NullValueTest extends TestCase
{
    public function testValue(): void
    {
        $value = new NullValue();
        static::assertNull($value->getNativeValue());
        static::assertSame('null', $value->getType());
    }
}
