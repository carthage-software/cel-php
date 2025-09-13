<?php

declare(strict_types=1);

namespace Cel\Tests\Runtime\Value;

use Cel\Runtime\Message\MessageInterface;
use Cel\Runtime\Value\MessageValue;
use Cel\Runtime\Value\StringValue;
use Cel\Runtime\Value\Value;
use Override;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\Attributes\UsesClass;
use PHPUnit\Framework\TestCase;

#[CoversClass(MessageValue::class)]
#[UsesClass(StringValue::class)]
final class MessageValueTest extends TestCase
{
    public function testValue(): void
    {
        $message = new class() implements MessageInterface {
            #[Override]
            public function toCelValue(): Value
            {
                return new MessageValue($this, ['name' => new StringValue('test')]);
            }

            #[Override]
            public static function fromCelFields(array $_fields): static
            {
                return new static();
            }
        };

        $value = new MessageValue($message, ['name' => new StringValue('test')]);
        static::assertSame($message, $value->getNativeValue());
        static::assertSame('message', $value->getType());
        static::assertTrue($value->isEqual($message->toCelValue()));
    }
}
