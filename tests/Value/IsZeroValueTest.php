<?php

declare(strict_types=1);

namespace Cel\Tests\Value;

use Cel\Tests\Fixture\UserMessage;
use Cel\Tests\Fixture\ZeroableMessage;
use Cel\Value\BooleanValue;
use Cel\Value\BytesValue;
use Cel\Value\DurationValue;
use Cel\Value\FloatValue;
use Cel\Value\IntegerValue;
use Cel\Value\ListValue;
use Cel\Value\MapValue;
use Cel\Value\MessageValue;
use Cel\Value\NullValue;
use Cel\Value\OptionalValue;
use Cel\Value\StringValue;
use Cel\Value\TimestampValue;
use Cel\Value\UnsignedIntegerValue;
use Cel\Value\Value;
use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\TestCase;
use Psl\DateTime\Duration;
use Psl\DateTime\Timestamp;

final class IsZeroValueTest extends TestCase
{
    #[DataProvider('provideValues')]
    public function testIsZeroValue(Value $value, bool $expected): void
    {
        static::assertSame($expected, $value->isZeroValue());
    }

    /**
     * @return iterable<string, array{0: Value, 1: bool}>
     */
    public static function provideValues(): iterable
    {
        yield 'null is zero' => [new NullValue(), true];

        yield 'false is zero' => [new BooleanValue(false), true];
        yield 'true is not zero' => [new BooleanValue(true), false];

        yield 'int 0 is zero' => [new IntegerValue(0), true];
        yield 'int 5 is not zero' => [new IntegerValue(5), false];

        yield 'uint 0 is zero' => [new UnsignedIntegerValue(0), true];
        yield 'uint 5 is not zero' => [new UnsignedIntegerValue(5), false];
        yield 'uint numeric-string 0 is zero' => [new UnsignedIntegerValue('0'), true];

        yield 'float 0.0 is zero' => [new FloatValue(0.0), true];
        yield 'float 1.5 is not zero' => [new FloatValue(1.5), false];

        yield 'empty string is zero' => [new StringValue(''), true];
        yield 'non-empty string is not zero' => [new StringValue('x'), false];

        yield 'empty bytes is zero' => [new BytesValue(''), true];
        yield 'non-empty bytes is not zero' => [new BytesValue('x'), false];

        yield 'empty list is zero' => [new ListValue([]), true];
        yield 'non-empty list is not zero' => [new ListValue([new IntegerValue(1)]), false];

        yield 'empty map is zero' => [new MapValue([]), true];
        yield 'non-empty map is not zero' => [new MapValue(['k' => new IntegerValue(1)]), false];

        yield 'zero duration is zero' => [new DurationValue(Duration::zero()), true];
        yield 'non-zero duration is not zero' => [new DurationValue(Duration::seconds(1)), false];

        yield 'epoch timestamp is not zero' => [new TimestampValue(Timestamp::fromParts(0, 0)), false];
        yield 'non-epoch timestamp is not zero' => [new TimestampValue(Timestamp::fromParts(1_700_000_000, 0)), false];

        yield 'empty optional is not zero' => [new OptionalValue(), false];
        yield 'present optional is not zero' => [new OptionalValue(new IntegerValue(0)), false];

        yield 'zero-aware message reporting zero' => [new MessageValue(new ZeroableMessage(true), []), true];
        yield 'zero-aware message reporting non-zero' => [new MessageValue(new ZeroableMessage(false), []), false];
        yield 'message without zero support is not zero' => [
            new MessageValue(new UserMessage('a', 'b'), [
                'name' => new StringValue('a'),
                'email' => new StringValue('b'),
            ]),
            false,
        ];
    }
}
