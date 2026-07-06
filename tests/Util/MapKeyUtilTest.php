<?php

declare(strict_types=1);

namespace Cel\Tests\Util;

use Cel\Util\MapKeyUtil;
use Cel\Value\BooleanValue;
use Cel\Value\FloatValue;
use Cel\Value\IntegerValue;
use Cel\Value\StringValue;
use Cel\Value\UnsignedIntegerValue;
use Cel\Value\Value;
use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\TestCase;

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
        yield 'bool is not a key type' => [false, new BooleanValue(true)];
    }

    #[DataProvider('provideResolveCases')]
    public function testResolve(null|int|string $expected, Value $value): void
    {
        static::assertSame($expected, MapKeyUtil::resolve($value));
    }

    /**
     * @return iterable<string, array{null|int|string, Value}>
     */
    public static function provideResolveCases(): iterable
    {
        yield 'string resolves to string' => ['a', new StringValue('a')];
        yield 'int resolves to int' => [1, new IntegerValue(1)];
        yield 'uint int resolves to int' => [1, new UnsignedIntegerValue(1)];
        yield 'uint numeric-string resolves to string' => [
            '18446744073709551615',
            new UnsignedIntegerValue('18446744073709551615'),
        ];
        yield 'integral double resolves to int' => [2, new FloatValue(2.0)];
        yield 'negative integral double resolves to int' => [-3, new FloatValue(-3.0)];
        yield 'non-integral double resolves to null' => [null, new FloatValue(1.5)];
        yield 'double above int range resolves to null' => [null, new FloatValue(1.0e19)];
        yield 'double below int range resolves to null' => [null, new FloatValue(-1.0e19)];
        yield 'infinite double resolves to null' => [null, new FloatValue(INF)];
        yield 'non-key type resolves to null' => [null, new BooleanValue(true)];
    }
}
