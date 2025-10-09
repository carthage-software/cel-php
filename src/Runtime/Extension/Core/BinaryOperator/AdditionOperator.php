<?php

declare(strict_types=1);

namespace Cel\Runtime\Extension\Core\BinaryOperator;

use Cel\Runtime\Operator\BinaryOperatorOverloadInterface;
use Cel\Runtime\Value\BytesValue;
use Cel\Runtime\Value\FloatValue;
use Cel\Runtime\Value\IntegerValue;
use Cel\Runtime\Value\ListValue;
use Cel\Runtime\Value\StringValue;
use Cel\Runtime\Value\UnsignedIntegerValue;
use Cel\Runtime\Value\Value;
use Cel\Runtime\Value\ValueKind;
use Cel\Syntax\Binary\BinaryOperatorKind;
use Override;

use function bcadd;

final readonly class AdditionOperator implements BinaryOperatorOverloadInterface
{
    #[Override]
    public function getOperator(): BinaryOperatorKind
    {
        return BinaryOperatorKind::Plus;
    }

    #[Override]
    public function getOverloads(): iterable
    {
        yield [ValueKind::Integer, ValueKind::Integer] =>
            /**
             * @param IntegerValue $left
             * @param IntegerValue $right
             */
            static fn(Value $left, Value $right): Value => new IntegerValue($left->value + $right->value);

        yield [ValueKind::UnsignedInteger, ValueKind::UnsignedInteger] =>
            /**
             * @param UnsignedIntegerValue $left
             * @param UnsignedIntegerValue $right
             */
            static fn(Value $left, Value $right): Value => new UnsignedIntegerValue(bcadd(
                (string) $left->value,
                (string) $right->value,
            ));

        yield [ValueKind::Float, ValueKind::Float] =>
            /**
             * @param FloatValue $left
             * @param FloatValue $right
             */
            static fn(Value $left, Value $right): Value => new FloatValue($left->value + $right->value);

        yield [ValueKind::String, ValueKind::String] =>
            /**
             * @param StringValue $left
             * @param StringValue $right
             */
            static fn(Value $left, Value $right): Value => new StringValue($left->value . $right->value);

        yield [ValueKind::Bytes, ValueKind::Bytes] =>
            /**
             * @param BytesValue $left
             * @param BytesValue $right
             */
            static fn(Value $left, Value $right): Value => new BytesValue($left->value . $right->value);

        yield [ValueKind::List, ValueKind::List] =>
            /**
             * @param ListValue $left
             * @param ListValue $right
             */
            static fn(Value $left, Value $right): Value => new ListValue([...$left->value, ...$right->value]);
    }
}
