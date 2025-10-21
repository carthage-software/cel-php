<?php

declare(strict_types=1);

namespace Cel\Extension\Decimal\BinaryOperator;

use Cel\Extension\Decimal\BinaryOperator\Handler\Addition\DecimalNumberPlusDecimalNumberHandler;
use Cel\Extension\Decimal\BinaryOperator\Handler\Addition\DecimalNumberPlusFloatHandler;
use Cel\Extension\Decimal\BinaryOperator\Handler\Addition\DecimalNumberPlusIntegerHandler;
use Cel\Extension\Decimal\BinaryOperator\Handler\Addition\DecimalNumberPlusUnsignedIntegerHandler;
use Cel\Extension\Decimal\BinaryOperator\Handler\Addition\FloatPlusDecimalNumberHandler;
use Cel\Extension\Decimal\BinaryOperator\Handler\Addition\IntegerPlusDecimalNumberHandler;
use Cel\Extension\Decimal\BinaryOperator\Handler\Addition\UnsignedIntegerPlusDecimalNumberHandler;
use Cel\Operator\BinaryOperatorOverloadInterface;
use Cel\Syntax\Binary\BinaryOperatorKind;
use Cel\Value\ValueKind;
use Override;

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
        // DecimalNumber + DecimalNumber
        yield [ValueKind::Message, ValueKind::Message] => new DecimalNumberPlusDecimalNumberHandler();

        // DecimalNumber + int
        yield [ValueKind::Message, ValueKind::Integer] => new DecimalNumberPlusIntegerHandler();

        // int + DecimalNumber
        yield [ValueKind::Integer, ValueKind::Message] => new IntegerPlusDecimalNumberHandler();

        // DecimalNumber + uint
        yield [ValueKind::Message, ValueKind::UnsignedInteger] => new DecimalNumberPlusUnsignedIntegerHandler();

        // uint + DecimalNumber
        yield [ValueKind::UnsignedInteger, ValueKind::Message] => new UnsignedIntegerPlusDecimalNumberHandler();

        // DecimalNumber + float
        yield [ValueKind::Message, ValueKind::Float] => new DecimalNumberPlusFloatHandler();

        // float + DecimalNumber
        yield [ValueKind::Float, ValueKind::Message] => new FloatPlusDecimalNumberHandler();
    }
}
