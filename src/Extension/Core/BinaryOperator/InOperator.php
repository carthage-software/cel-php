<?php

declare(strict_types=1);

namespace Cel\Extension\Core\BinaryOperator;

use Cel\Extension\Core\BinaryOperator\Handler\InOperator\BooleanListHandler;
use Cel\Extension\Core\BinaryOperator\Handler\InOperator\BytesListHandler;
use Cel\Extension\Core\BinaryOperator\Handler\InOperator\DurationListHandler;
use Cel\Extension\Core\BinaryOperator\Handler\InOperator\FloatListHandler;
use Cel\Extension\Core\BinaryOperator\Handler\InOperator\IntegerListHandler;
use Cel\Extension\Core\BinaryOperator\Handler\InOperator\ListListHandler;
use Cel\Extension\Core\BinaryOperator\Handler\InOperator\MapListHandler;
use Cel\Extension\Core\BinaryOperator\Handler\InOperator\MessageListHandler;
use Cel\Extension\Core\BinaryOperator\Handler\InOperator\NullListHandler;
use Cel\Extension\Core\BinaryOperator\Handler\InOperator\StringListHandler;
use Cel\Extension\Core\BinaryOperator\Handler\InOperator\StringMapHandler;
use Cel\Extension\Core\BinaryOperator\Handler\InOperator\TimestampListHandler;
use Cel\Extension\Core\BinaryOperator\Handler\InOperator\UnsignedIntegerListHandler;
use Cel\Operator\BinaryOperatorOverloadInterface;
use Cel\Syntax\Binary\BinaryOperatorKind;
use Cel\Value\ValueKind;
use Override;

final readonly class InOperator implements BinaryOperatorOverloadInterface
{
    #[Override]
    public function getOperator(): BinaryOperatorKind
    {
        return BinaryOperatorKind::In;
    }

    #[Override]
    public function getOverloads(): iterable
    {
        yield [ValueKind::Integer, ValueKind::List] => new IntegerListHandler();
        yield [ValueKind::UnsignedInteger, ValueKind::List] => new UnsignedIntegerListHandler();
        yield [ValueKind::Float, ValueKind::List] => new FloatListHandler();
        yield [ValueKind::String, ValueKind::List] => new StringListHandler();
        yield [ValueKind::Bytes, ValueKind::List] => new BytesListHandler();
        yield [ValueKind::Boolean, ValueKind::List] => new BooleanListHandler();
        yield [ValueKind::Null, ValueKind::List] => new NullListHandler();
        yield [ValueKind::List, ValueKind::List] => new ListListHandler();
        yield [ValueKind::Map, ValueKind::List] => new MapListHandler();
        yield [ValueKind::Message, ValueKind::List] => new MessageListHandler();
        yield [ValueKind::Timestamp, ValueKind::List] => new TimestampListHandler();
        yield [ValueKind::Duration, ValueKind::List] => new DurationListHandler();
        yield [ValueKind::String, ValueKind::Map] => new StringMapHandler();
    }
}
