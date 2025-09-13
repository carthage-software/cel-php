<?php

declare(strict_types=1);

namespace Cel\Tests\Runtime\Value;

use Cel\Runtime\Value\FloatValue;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\TestCase;

#[CoversClass(FloatValue::class)]
final class FloatValueTest extends TestCase
{
    public function testValue(): void
    {
        $value = new FloatValue(1.23);
        static::assertSame(1.23, $value->getNativeValue());
        static::assertSame('float', $value->getType());
    }
}
