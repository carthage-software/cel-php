<?php

declare(strict_types=1);

namespace Cel\Extension\Decimal\BinaryOperator;

use Cel\Extension\Decimal\BinaryOperator\Handler\Subtraction\DecimalNumberMinusDecimalNumberHandler;
use Cel\Extension\Decimal\BinaryOperator\Handler\Subtraction\DecimalNumberMinusFloatHandler;
use Cel\Extension\Decimal\BinaryOperator\Handler\Subtraction\DecimalNumberMinusIntegerHandler;
use Cel\Extension\Decimal\BinaryOperator\Handler\Subtraction\DecimalNumberMinusUnsignedIntegerHandler;
use Cel\Extension\Decimal\BinaryOperator\Handler\Subtraction\FloatMinusDecimalNumberHandler;
use Cel\Extension\Decimal\BinaryOperator\Handler\Subtraction\IntegerMinusDecimalNumberHandler;
use Cel\Extension\Decimal\BinaryOperator\Handler\Subtraction\UnsignedIntegerMinusDecimalNumberHandler;
use Cel\Operator\BinaryOperatorOverloadInterface;
use Cel\Syntax\Binary\BinaryOperatorKind;
use Cel\Value\ValueKind;
use Override;

final readonly class SubtractionOperator implements BinaryOperatorOverloadInterface
{
    #[Override]
    public function getOperator(): BinaryOperatorKind
    {
        return BinaryOperatorKind::Minus;
    }

    #[Override]
    public function getOverloads(): iterable
    {
        // DecimalNumber - DecimalNumber
        yield [ValueKind::Message, ValueKind::Message] => new DecimalNumberMinusDecimalNumberHandler();

        // DecimalNumber - int
        yield [ValueKind::Message, ValueKind::Integer] => new DecimalNumberMinusIntegerHandler();

        // int - DecimalNumber
        yield [ValueKind::Integer, ValueKind::Message] => new IntegerMinusDecimalNumberHandler();

        // DecimalNumber - uint
        yield [ValueKind::Message, ValueKind::UnsignedInteger] => new DecimalNumberMinusUnsignedIntegerHandler();

        // uint - DecimalNumber
        yield [ValueKind::UnsignedInteger, ValueKind::Message] => new UnsignedIntegerMinusDecimalNumberHandler();

        // DecimalNumber - float
        yield [ValueKind::Message, ValueKind::Float] => new DecimalNumberMinusFloatHandler();

        // float - DecimalNumber
        yield [ValueKind::Float, ValueKind::Message] => new FloatMinusDecimalNumberHandler();
    }
}
