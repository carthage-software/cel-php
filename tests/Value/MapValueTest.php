<?php

declare(strict_types=1);

namespace Cel\Tests\Value;

use Cel\Exception\UnsupportedOperationException;
use Cel\Value\IntegerValue;
use Cel\Value\MapValue;
use Cel\Value\StringValue;
use Cel\Value\ValueKind;
use PHPUnit\Framework\TestCase;

final class MapValueTest extends TestCase
{
    /**
     * @mago-expect analysis:possibly-undefined-array-index
     */
    public function testgetRawValue(): void
    {
        $map = new MapValue(['key' => new StringValue('value')]);

        $native = $map->getRawValue();

        static::assertArrayHasKey('key', $native);
        static::assertSame('value', $native['key']);
    }

    public function testGetKind(): void
    {
        $map = new MapValue([]);

        static::assertSame(ValueKind::Map, $map->getKind());
    }

    public function testGetType(): void
    {
        $map = new MapValue([]);

        static::assertSame('map', $map->getType());
    }

    public function testIsEqualWithSameMap(): void
    {
        $map1 = new MapValue(['a' => new IntegerValue(1), 'b' => new IntegerValue(2)]);
        $map2 = new MapValue(['a' => new IntegerValue(1), 'b' => new IntegerValue(2)]);

        static::assertTrue($map1->isEqual($map2));
    }

    public function testIsEqualWithDifferentValues(): void
    {
        $map1 = new MapValue(['a' => new IntegerValue(1)]);
        $map2 = new MapValue(['a' => new IntegerValue(2)]);

        static::assertFalse($map1->isEqual($map2));
    }

    public function testIsEqualWithDifferentKeys(): void
    {
        $map1 = new MapValue(['a' => new IntegerValue(1)]);
        $map2 = new MapValue(['b' => new IntegerValue(1)]);

        static::assertFalse($map1->isEqual($map2));
    }

    public function testIsEqualWithNonMapThrowsException(): void
    {
        $map = new MapValue([]);
        $int = new IntegerValue(42);

        $this->expectException(UnsupportedOperationException::class);
        $map->isEqual($int);
    }

    public function testIsLessThanThrowsException(): void
    {
        $map1 = new MapValue([]);
        $map2 = new MapValue([]);

        $this->expectException(UnsupportedOperationException::class);
        $map1->isLessThan($map2);
    }

    public function testIsGreaterThanThrowsException(): void
    {
        $map1 = new MapValue([]);
        $map2 = new MapValue([]);

        $this->expectException(UnsupportedOperationException::class);
        $map1->isGreaterThan($map2);
    }

    public function testHas(): void
    {
        $map = new MapValue(['key' => new StringValue('value')]);

        static::assertTrue($map->has('key'));
        static::assertFalse($map->has('missing'));
    }

    public function testGet(): void
    {
        $value = new StringValue('value');
        $map = new MapValue(['key' => $value]);

        $result = $map->get('key');

        static::assertSame($value, $result);
    }

    public function testGetMissingKeyReturnsNull(): void
    {
        $map = new MapValue([]);

        $result = $map->get('missing');

        static::assertNull($result);
    }
}
