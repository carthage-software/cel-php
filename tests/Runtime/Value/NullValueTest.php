<?php

declare(strict_types=1);

namespace Cel\Tests\Runtime\Value;

use Cel\Exception\UnsupportedOperationException;
use Cel\Value\IntegerValue;
use Cel\Value\NullValue;
use Cel\Value\StringValue;
use Cel\Value\Value;
use Cel\Value\ValueKind;
use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\TestCase;

final class NullValueTest extends TestCase
{
    public function testValue(): void
    {
        $value = new NullValue();
        static::assertNull($value->getRawValue());
        static::assertSame('null_type', $value->getType());
    }

    public function testGetKind(): void
    {
        $value = new NullValue();
        static::assertSame(ValueKind::Null, $value->getKind());
    }

    #[DataProvider('provideEqualityCases')]
    public function testIsEqual(bool $expected, Value $other): void
    {
        static::assertSame($expected, new NullValue()->isEqual($other));
    }

    /**
     * @return iterable<string, array{bool, Value}>
     */
    public static function provideEqualityCases(): iterable
    {
        yield 'null == null' => [true, new NullValue()];
        yield 'null == int' => [false, new IntegerValue(42)];
        yield 'null == string' => [false, new StringValue('x')];
    }

    public function testIsLessThanThrowsException(): void
    {
        $null1 = new NullValue();
        $null2 = new NullValue();

        $this->expectException(UnsupportedOperationException::class);
        $null1->isLessThan($null2);
    }

    public function testIsGreaterThanThrowsException(): void
    {
        $null1 = new NullValue();
        $null2 = new NullValue();

        $this->expectException(UnsupportedOperationException::class);
        $null1->isGreaterThan($null2);
    }
}
