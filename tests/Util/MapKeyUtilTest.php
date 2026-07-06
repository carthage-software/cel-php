<?php

declare(strict_types=1);

namespace Cel\Tests\Util;

use Cel\Util\MapKeyUtil;
use Cel\Value\BooleanValue;
use Cel\Value\FloatValue;
use Cel\Value\IntegerValue;
use Cel\Value\NullValue;
use Cel\Value\StringValue;
use Cel\Value\UnsignedIntegerValue;
use Cel\Value\Value;
use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\TestCase;

use const INF;

final class MapKeyUtilTest extends TestCase
{
    #[DataProvider('provideIsKeyTypeCases')]
    public function testIsKeyType(bool $expected, Value $value): void
    {
        static::assertSame($expected, MapKeyUtil::isKeyType($value));
    }

    /**
     * @return iterable<string, array{bool, Value}>
     */
    public static function provideIsKeyTypeCases(): iterable
    {
        yield 'string is a key type' => [true, new StringValue('a')];
        yield 'int is a key type' => [true, new IntegerValue(1)];
        yield 'uint is a key type' => [true, new UnsignedIntegerValue(1)];
        yield 'double is a key type' => [true, new FloatValue(1.0)];
        yield 'bool is a key type' => [true, new BooleanValue(true)];
        yield 'null is not a key type' => [false, new NullValue()];
    }

    #[DataProvider('provideResolveCases')]
    public function testResolve(null|string $expected, Value $value): void
    {
        static::assertSame($expected, MapKeyUtil::resolve($value));
    }

    /**
     * @return iterable<string, array{null|string, Value}>
     */
    public static function provideResolveCases(): iterable
    {
        yield 'string resolves to a tagged string' => ["\xFFs:a", new StringValue('a')];
        yield 'int resolves to a tagged number' => ["\xFFn:1", new IntegerValue(1)];
        yield 'uint int resolves to a tagged number' => ["\xFFn:1", new UnsignedIntegerValue(1)];
        yield 'uint numeric-string resolves to a tagged number' => [
            "\xFFn:18446744073709551615",
            new UnsignedIntegerValue('18446744073709551615'),
        ];
        yield 'integral double resolves to a tagged number' => ["\xFFn:2", new FloatValue(2.0)];
        yield 'negative integral double resolves to a tagged number' => ["\xFFn:-3", new FloatValue(-3.0)];
        yield 'non-integral double resolves to null' => [null, new FloatValue(1.5)];
        yield 'double above int range resolves to null' => [null, new FloatValue(1.0e19)];
        yield 'double below int range resolves to null' => [null, new FloatValue(-1.0e19)];
        yield 'infinite double resolves to null' => [null, new FloatValue(INF)];
        yield 'true resolves to a tagged boolean' => ["\xFFb:1", new BooleanValue(true)];
        yield 'false resolves to a tagged boolean' => ["\xFFb:0", new BooleanValue(false)];
        yield 'non-key type resolves to null' => [null, new NullValue()];
    }

    #[DataProvider('provideResolveIndexCases')]
    public function testResolveIndex(null|int $expected, Value $value): void
    {
        static::assertSame($expected, MapKeyUtil::resolveIndex($value));
    }

    /**
     * @return iterable<string, array{null|int, Value}>
     */
    public static function provideResolveIndexCases(): iterable
    {
        yield 'int resolves to its position' => [5, new IntegerValue(5)];
        yield 'uint within range resolves to its position' => [5, new UnsignedIntegerValue(5)];
        yield 'uint beyond int range resolves to null' => [
            null,
            new UnsignedIntegerValue('18446744073709551615'),
        ];
        yield 'integral double resolves to its position' => [2, new FloatValue(2.0)];
        yield 'non-integral double resolves to null' => [null, new FloatValue(1.5)];
        yield 'string does not resolve to a position' => [null, new StringValue('1')];
        yield 'bool does not resolve to a position' => [null, new BooleanValue(true)];
        yield 'null does not resolve to a position' => [null, new NullValue()];
    }

    #[DataProvider('provideKeyToValueCases')]
    public function testKeyToValue(Value $expected, int|string $key): void
    {
        static::assertTrue($expected->isEqual(MapKeyUtil::keyToValue($key)));
    }

    /**
     * @return iterable<string, array{Value, int|string}>
     */
    public static function provideKeyToValueCases(): iterable
    {
        yield 'tagged string becomes a string' => [new StringValue('a'), "\xFFs:a"];
        yield 'tagged number becomes an integer' => [new IntegerValue(7), "\xFFn:7"];
        yield 'large tagged number becomes an unsigned integer' => [
            new UnsignedIntegerValue('18446744073709551615'),
            "\xFFn:18446744073709551615",
        ];
        yield 'tagged true becomes true' => [new BooleanValue(true), "\xFFb:1"];
        yield 'tagged false becomes false' => [new BooleanValue(false), "\xFFb:0"];
        yield 'native integer key becomes an integer' => [new IntegerValue(7), 7];
        yield 'untagged string key becomes a string' => [new StringValue('plain'), 'plain'];
    }

    #[DataProvider('provideKeyToRawCases')]
    public function testKeyToRaw(int|string $expected, int|string $key): void
    {
        static::assertSame($expected, MapKeyUtil::keyToRaw($key));
    }

    /**
     * @return iterable<string, array{int|string, int|string}>
     */
    public static function provideKeyToRawCases(): iterable
    {
        yield 'tagged string decodes to the raw string' => ['a', "\xFFs:a"];
        yield 'tagged number decodes to an int' => [7, "\xFFn:7"];
        yield 'large tagged number decodes to a decimal string' => [
            '18446744073709551615',
            "\xFFn:18446744073709551615",
        ];
        yield 'tagged true decodes to one' => [1, "\xFFb:1"];
        yield 'tagged false decodes to zero' => [0, "\xFFb:0"];
        yield 'native integer key is unchanged' => [7, 7];
        yield 'untagged string key is unchanged' => ['plain', 'plain'];
    }
}
