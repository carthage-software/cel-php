<?php

declare(strict_types=1);

namespace Cel\Tests\Runtime\Value;

use Cel\Runtime\Value\MapValue;
use Cel\Runtime\Value\StringValue;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\Attributes\UsesClass;
use PHPUnit\Framework\TestCase;

#[CoversClass(MapValue::class)]
#[UsesClass(StringValue::class)]
final class MapValueTest extends TestCase
{
    public function testValue(): void
    {
        $value = new MapValue(['a' => new StringValue('b')]);
        static::assertSame(['a' => 'b'], $value->getNativeValue());
        static::assertSame('map', $value->getType());
    }
}
