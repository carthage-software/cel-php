<?php

declare(strict_types=1);

namespace Cel\Extension\Core\BinaryOperator;

use Cel\Extension\Core\BinaryOperator\Handler\EqualityOperator\BooleanBooleanHandler;
use Cel\Extension\Core\BinaryOperator\Handler\EqualityOperator\BytesBytesHandler;
use Cel\Extension\Core\BinaryOperator\Handler\EqualityOperator\DurationDurationHandler;
use Cel\Extension\Core\BinaryOperator\Handler\EqualityOperator\FloatFloatHandler;
use Cel\Extension\Core\BinaryOperator\Handler\EqualityOperator\IntegerIntegerHandler;
use Cel\Extension\Core\BinaryOperator\Handler\EqualityOperator\ListListHandler;
use Cel\Extension\Core\BinaryOperator\Handler\EqualityOperator\MapMapHandler;
use Cel\Extension\Core\BinaryOperator\Handler\EqualityOperator\MessageMessageHandler;
use Cel\Extension\Core\BinaryOperator\Handler\EqualityOperator\NullNullHandler;
use Cel\Extension\Core\BinaryOperator\Handler\EqualityOperator\StringStringHandler;
use Cel\Extension\Core\BinaryOperator\Handler\EqualityOperator\TimestampTimestampHandler;
use Cel\Extension\Core\BinaryOperator\Handler\EqualityOperator\UnsignedIntegerUnsignedIntegerHandler;
use Cel\Operator\BinaryOperatorOverloadInterface;
use Cel\Syntax\Binary\BinaryOperatorKind;
use Cel\Value\ValueKind;
use Override;

final readonly class EqualityOperator implements BinaryOperatorOverloadInterface
{
    public function __construct(
        private BinaryOperatorKind $operator,
    ) {}

    #[Override]
    public function getOperator(): BinaryOperatorKind
    {
        return $this->operator;
    }

    #[Override]
    public function getOverloads(): iterable
    {
        $isEqual = $this->operator === BinaryOperatorKind::Equal;

        yield [ValueKind::Integer, ValueKind::Integer] => new IntegerIntegerHandler($isEqual);
        yield [ValueKind::UnsignedInteger, ValueKind::UnsignedInteger] => new UnsignedIntegerUnsignedIntegerHandler(
            $isEqual,
        );
        yield [ValueKind::Float, ValueKind::Float] => new FloatFloatHandler($isEqual);
        yield [ValueKind::String, ValueKind::String] => new StringStringHandler($isEqual);
        yield [ValueKind::Bytes, ValueKind::Bytes] => new BytesBytesHandler($isEqual);
        yield [ValueKind::Boolean, ValueKind::Boolean] => new BooleanBooleanHandler($isEqual);
        yield [ValueKind::Null, ValueKind::Null] => new NullNullHandler($isEqual);
        yield [ValueKind::List, ValueKind::List] => new ListListHandler($isEqual);
        yield [ValueKind::Map, ValueKind::Map] => new MapMapHandler($isEqual);
        yield [ValueKind::Message, ValueKind::Message] => new MessageMessageHandler($isEqual);
        yield [ValueKind::Timestamp, ValueKind::Timestamp] => new TimestampTimestampHandler($isEqual);
        yield [ValueKind::Duration, ValueKind::Duration] => new DurationDurationHandler($isEqual);
    }
}
