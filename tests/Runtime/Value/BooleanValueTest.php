<?php

declare(strict_types=1);

namespace Cel\Tests\Runtime\Value;

use Cel\Runtime\Value\BooleanValue;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\TestCase;

#[CoversClass(BooleanValue::class)]
final class BooleanValueTest extends TestCase
{
    public function testValue(): void
    {
        $value = new BooleanValue(true);
        static::assertTrue($value->getNativeValue());
        static::assertSame('bool', $value->getType());
    }
}
