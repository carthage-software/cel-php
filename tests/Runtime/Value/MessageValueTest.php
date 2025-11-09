<?php

declare(strict_types=1);

namespace Cel\Tests\Runtime\Value;

use Cel\Message\MessageInterface;
use Cel\Value\MessageValue;
use Cel\Value\StringValue;
use Cel\Value\Value;
use Override;
use PHPUnit\Framework\TestCase;

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
            public static function fromCelFields(array $fields): static
            {
                return new static();
            }
        };

        $value = new MessageValue($message, ['name' => new StringValue('test')]);
        static::assertSame($message, $value->getRawValue());
        static::assertSame('message', $value->getType());
        static::assertTrue($value->isEqual($message->toCelValue()));
    }
}
