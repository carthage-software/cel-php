<?php

declare(strict_types=1);

namespace Cel\Tests\Runtime\Value;

use Cel\Exception\IncompatibleValueTypeException;
use Cel\Message\MessageInterface;
use Cel\Value\BooleanValue;
use Cel\Value\FloatValue;
use Cel\Value\IntegerValue;
use Cel\Value\ListValue;
use Cel\Value\MapValue;
use Cel\Value\MessageValue;
use Cel\Value\NullValue;
use Cel\Value\StringValue;
use Cel\Value\Value;
use Override;
use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\TestCase;
use stdClass;

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
        yield 'message' => [
            new class() implements MessageInterface {
                #[Override]
                public function toCelValue(): Value
                {
                    return new MessageValue($this, []);
                }

                #[Override]
                public static function fromCelFields(array $fields): static
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
