<?php

declare(strict_types=1);

namespace Cel\Runtime\Extension\Core\BinaryOperator;

use Cel\Runtime\Operator\BinaryOperatorOverloadInterface;
use Cel\Runtime\Value\FloatValue;
use Cel\Runtime\Value\IntegerValue;
use Cel\Runtime\Value\UnsignedIntegerValue;
use Cel\Runtime\Value\Value;
use Cel\Runtime\Value\ValueKind;
use Cel\Syntax\Binary\BinaryOperatorKind;
use Override;

use function bcmul;

final readonly class MultiplicationOperator implements BinaryOperatorOverloadInterface
{
    #[Override]
    public function getOperator(): BinaryOperatorKind
    {
        return BinaryOperatorKind::Multiply;
    }

    #[Override]
    public function getOverloads(): iterable
    {
        yield [ValueKind::Integer, ValueKind::Integer] =>
            /**
             * @param IntegerValue $left
             * @param IntegerValue $right
             */
            static fn(Value $left, Value $right): Value => new IntegerValue($left->value * $right->value);

        yield [ValueKind::UnsignedInteger, ValueKind::UnsignedInteger] =>
            /**
             * @param UnsignedIntegerValue $left
             * @param UnsignedIntegerValue $right
             */
            static fn(Value $left, Value $right): Value => new UnsignedIntegerValue(bcmul(
                (string) $left->value,
                (string) $right->value,
            ));

        yield [ValueKind::Float, ValueKind::Float] =>
            /**
             * @param FloatValue $left
             * @param FloatValue $right
             */
            static fn(Value $left, Value $right): Value => new FloatValue($left->value * $right->value);
    }
}
