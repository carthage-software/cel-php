<?php

declare(strict_types=1);

namespace Cel\Extension\Decimal\BinaryOperator;

use Cel\Extension\Decimal\BinaryOperator\Handler\Multiplication\DecimalNumberMultiplyDecimalNumberHandler;
use Cel\Extension\Decimal\BinaryOperator\Handler\Multiplication\DecimalNumberMultiplyFloatHandler;
use Cel\Extension\Decimal\BinaryOperator\Handler\Multiplication\DecimalNumberMultiplyIntegerHandler;
use Cel\Extension\Decimal\BinaryOperator\Handler\Multiplication\DecimalNumberMultiplyUnsignedIntegerHandler;
use Cel\Extension\Decimal\BinaryOperator\Handler\Multiplication\FloatMultiplyDecimalNumberHandler;
use Cel\Extension\Decimal\BinaryOperator\Handler\Multiplication\IntegerMultiplyDecimalNumberHandler;
use Cel\Extension\Decimal\BinaryOperator\Handler\Multiplication\UnsignedIntegerMultiplyDecimalNumberHandler;
use Cel\Operator\BinaryOperatorOverloadInterface;
use Cel\Syntax\Binary\BinaryOperatorKind;
use Cel\Value\ValueKind;
use Override;

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
        // DecimalNumber * DecimalNumber
        yield [ValueKind::Message, ValueKind::Message] => new DecimalNumberMultiplyDecimalNumberHandler();

        // DecimalNumber * int
        yield [ValueKind::Message, ValueKind::Integer] => new DecimalNumberMultiplyIntegerHandler();

        // int * DecimalNumber
        yield [ValueKind::Integer, ValueKind::Message] => new IntegerMultiplyDecimalNumberHandler();

        // DecimalNumber * uint
        yield [ValueKind::Message, ValueKind::UnsignedInteger] => new DecimalNumberMultiplyUnsignedIntegerHandler();

        // uint * DecimalNumber
        yield [ValueKind::UnsignedInteger, ValueKind::Message] => new UnsignedIntegerMultiplyDecimalNumberHandler();

        // DecimalNumber * float
        yield [ValueKind::Message, ValueKind::Float] => new DecimalNumberMultiplyFloatHandler();

        // float * DecimalNumber
        yield [ValueKind::Float, ValueKind::Message] => new FloatMultiplyDecimalNumberHandler();
    }
}
