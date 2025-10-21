<?php

declare(strict_types=1);

namespace Cel\Tests\Runtime\Value;

use Cel\Value\ValueKind;
use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\TestCase;

final class ValueKindTest extends TestCase
{
    #[DataProvider('provideIsAggregateCases')]
    public function testIsAggregate(ValueKind $kind, bool $expected): void
    {
        static::assertSame($expected, $kind->isAggregate());
    }

    public static function provideIsAggregateCases(): iterable
    {
        yield 'boolean is not aggregate' => [ValueKind::Boolean, false];
        yield 'bytes is not aggregate' => [ValueKind::Bytes, false];
        yield 'float is not aggregate' => [ValueKind::Float, false];
        yield 'integer is not aggregate' => [ValueKind::Integer, false];
        yield 'list is aggregate' => [ValueKind::List, true];
        yield 'map is aggregate' => [ValueKind::Map, true];
        yield 'message is aggregate' => [ValueKind::Message, true];
        yield 'null is not aggregate' => [ValueKind::Null, false];
        yield 'string is not aggregate' => [ValueKind::String, false];
        yield 'unsigned integer is not aggregate' => [ValueKind::UnsignedInteger, false];
        yield 'duration is not aggregate' => [ValueKind::Duration, false];
        yield 'timestamp is not aggregate' => [ValueKind::Timestamp, false];
    }
}
