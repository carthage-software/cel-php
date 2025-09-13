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
                return new MessageValue(static::class, ['name' => new StringValue('test')]);
            }

            #[Override]
            public static function fromCelValue(Value $value): static
            {
                return new static();
            }
        };

        $value = new MessageValue($message::class, ['name' => new StringValue('test')]);
        static::assertInstanceOf($message::class, $value->getNativeValue());
        static::assertSame('message', $value->getType());
    }
}
