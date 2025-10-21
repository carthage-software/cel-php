<?php

declare(strict_types=1);

namespace Cel\Tests\Runtime\Value;

use Cel\Value\MapValue;
use Cel\Value\StringValue;
use PHPUnit\Framework\TestCase;

final class MapValueTest extends TestCase
{
    public function testValue(): void
    {
        $value = new MapValue(['a' => new StringValue('b')]);
        static::assertSame(['a' => 'b'], $value->getRawValue());
        static::assertSame('map', $value->getType());
    }
}
