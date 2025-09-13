<?php

declare(strict_types=1);

namespace Cel\Tests\Runtime\Value;

use Cel\Runtime\Exception\IncompatibleValueTypeException;
use Cel\Runtime\Message\MessageInterface;
use Cel\Runtime\Value\BooleanValue;
use Cel\Runtime\Value\BytesValue;
use Cel\Runtime\Value\FloatValue;
use Cel\Runtime\Value\IntegerValue;
use Cel\Runtime\Value\ListValue;
use Cel\Runtime\Value\MapValue;
use Cel\Runtime\Value\MessageValue;
use Cel\Runtime\Value\NullValue;
use Cel\Runtime\Value\StringValue;
use Cel\Runtime\Value\Value;
use Override;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\Attributes\Medium;
use PHPUnit\Framework\Attributes\UsesClass;
use PHPUnit\Framework\TestCase;
use stdClass;

#[CoversClass(Value::class)]
#[Medium]
final class ValueTest extends TestCase
{
    /**
     * @param class-string<Value> $expectedClass
     */
    #[DataProvider('provideFromCases')]
    public function testFrom(mixed $nativeValue, string $expectedClass): void
    {
        $value = Value::from($nativeValue);

        static::assertInstanceOf($expectedClass, $value);
    }

    public static function provideFromCases(): iterable
    {
        yield 'null' => [null, NullValue::class];
        yield 'bool' => [true, BooleanValue::class];
        yield 'int' => [123, IntegerValue::class];
        yield 'float' => [1.23, FloatValue::class];
        yield 'string' => ['hello', StringValue::class];
        yield 'list' => [[], ListValue::class];
        yield 'map' => [['a' => 'b'], MapValue::class];
        yield 'message' =>
            [
                new class() implements MessageInterface {
                    #[Override]
                    public function toCelValue(): Value
                    {
                        return new MessageValue($this, []);
                    }

                    #[Override]
                    public static function fromCelFields(array $_fields): static
                    {
                        return new static();
                    }
                },
                MessageValue::class,
            ];
    }

    public function testFromUnsupportedObject(): void
    {
        $this->expectException(IncompatibleValueTypeException::class);

        Value::from(new stdClass());
    }
}
