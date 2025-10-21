<?php

declare(strict_types=1);

namespace Cel\Tests\Value;

use Cel\Exception\UnsupportedOperationException;
use Cel\Message\MessageInterface;
use Cel\Value\IntegerValue;
use Cel\Value\MessageValue;
use Cel\Value\StringValue;
use Cel\Value\Value;
use Cel\Value\ValueKind;
use PHPUnit\Framework\TestCase;

final class MessageValueTest extends TestCase
{
    public function testgetRawValue(): void
    {
        $message = new class implements MessageInterface {
            #[\Override]
            public function toCelValue(): Value
            {
                return new MessageValue($this, []);
            }

            #[\Override]
            public static function fromCelFields(array $fields): static
            {
                return new self();
            }
        };

        $value = new MessageValue($message, []);

        static::assertSame($message, $value->getRawValue());
    }

    public function testGetKind(): void
    {
        $message = $this->createMockMessage();
        $value = new MessageValue($message, []);

        static::assertSame(ValueKind::Message, $value->getKind());
    }

    public function testGetType(): void
    {
        $message = $this->createMockMessage();
        $value = new MessageValue($message, []);

        static::assertSame('message', $value->getType());
    }

    public function testIsEqualWithSameMessage(): void
    {
        $message1 = $this->createMockMessage();
        $message2 = $this->createMockMessage();

        $val1 = new MessageValue($message1, ['field' => new IntegerValue(42)]);
        $val2 = new MessageValue($message2, ['field' => new IntegerValue(42)]);

        static::assertTrue($val1->isEqual($val2));
    }

    public function testIsEqualWithDifferentMessageClass(): void
    {
        $message1 = $this->createMockMessage();
        $message2 = new class implements MessageInterface {
            #[\Override]
            public function toCelValue(): Value
            {
                return new MessageValue($this, []);
            }

            #[\Override]
            public static function fromCelFields(array $fields): static
            {
                return new self();
            }
        };

        $val1 = new MessageValue($message1, []);
        $val2 = new MessageValue($message2, []);

        static::assertFalse($val1->isEqual($val2));
    }

    public function testIsEqualWithDifferentFields(): void
    {
        $message1 = $this->createMockMessage();
        $message2 = $this->createMockMessage();

        $val1 = new MessageValue($message1, ['field' => new IntegerValue(1)]);
        $val2 = new MessageValue($message2, ['field' => new IntegerValue(2)]);

        static::assertFalse($val1->isEqual($val2));
    }

    public function testIsEqualWithNonMessageThrowsException(): void
    {
        $message = $this->createMockMessage();
        $val = new MessageValue($message, []);
        $int = new IntegerValue(42);

        $this->expectException(UnsupportedOperationException::class);
        $val->isEqual($int);
    }

    public function testIsLessThanThrowsException(): void
    {
        $message1 = $this->createMockMessage();
        $message2 = $this->createMockMessage();

        $val1 = new MessageValue($message1, []);
        $val2 = new MessageValue($message2, []);

        $this->expectException(UnsupportedOperationException::class);
        $val1->isLessThan($val2);
    }

    public function testIsGreaterThanThrowsException(): void
    {
        $message1 = $this->createMockMessage();
        $message2 = $this->createMockMessage();

        $val1 = new MessageValue($message1, []);
        $val2 = new MessageValue($message2, []);

        $this->expectException(UnsupportedOperationException::class);
        $val1->isGreaterThan($val2);
    }

    public function testHasField(): void
    {
        $message = $this->createMockMessage();
        $value = new MessageValue($message, ['field' => new StringValue('value')]);

        static::assertTrue($value->hasField('field'));
        static::assertFalse($value->hasField('missing'));
    }

    public function testGetField(): void
    {
        $message = $this->createMockMessage();
        $fieldValue = new StringValue('value');
        $value = new MessageValue($message, ['field' => $fieldValue]);

        $result = $value->getField('field');

        static::assertSame($fieldValue, $result);
    }

    public function testGetFieldMissingReturnsNull(): void
    {
        $message = $this->createMockMessage();
        $value = new MessageValue($message, []);

        $result = $value->getField('missing');

        static::assertNull($result);
    }

    private function createMockMessage(): MessageInterface
    {
        return new class implements MessageInterface {
            #[\Override]
            public function toCelValue(): Value
            {
                return new MessageValue($this, []);
            }

            #[\Override]
            public static function fromCelFields(array $fields): static
            {
                return new self();
            }
        };
    }
}
