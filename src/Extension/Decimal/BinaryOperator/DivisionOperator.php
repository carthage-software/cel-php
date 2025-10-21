<?php

declare(strict_types=1);

namespace Cel\Extension\Decimal\BinaryOperator;

use Cel\Extension\Decimal\BinaryOperator\Handler\Division\DecimalNumberDivideDecimalNumberHandler;
use Cel\Extension\Decimal\BinaryOperator\Handler\Division\DecimalNumberDivideFloatHandler;
use Cel\Extension\Decimal\BinaryOperator\Handler\Division\DecimalNumberDivideIntegerHandler;
use Cel\Extension\Decimal\BinaryOperator\Handler\Division\DecimalNumberDivideUnsignedIntegerHandler;
use Cel\Extension\Decimal\BinaryOperator\Handler\Division\FloatDivideDecimalNumberHandler;
use Cel\Extension\Decimal\BinaryOperator\Handler\Division\IntegerDivideDecimalNumberHandler;
use Cel\Extension\Decimal\BinaryOperator\Handler\Division\UnsignedIntegerDivideDecimalNumberHandler;
use Cel\Operator\BinaryOperatorOverloadInterface;
use Cel\Syntax\Binary\BinaryOperatorKind;
use Cel\Value\ValueKind;
use Override;

final readonly class DivisionOperator implements BinaryOperatorOverloadInterface
{
    #[Override]
    public function getOperator(): BinaryOperatorKind
    {
        return BinaryOperatorKind::Divide;
    }

    #[Override]
    public function getOverloads(): iterable
    {
        // DecimalNumber / DecimalNumber
        yield [ValueKind::Message, ValueKind::Message] => new DecimalNumberDivideDecimalNumberHandler();

        // DecimalNumber / int
        yield [ValueKind::Message, ValueKind::Integer] => new DecimalNumberDivideIntegerHandler();

        // int / DecimalNumber
        yield [ValueKind::Integer, ValueKind::Message] => new IntegerDivideDecimalNumberHandler();

        // DecimalNumber / uint
        yield [ValueKind::Message, ValueKind::UnsignedInteger] => new DecimalNumberDivideUnsignedIntegerHandler();

        // uint / DecimalNumber
        yield [ValueKind::UnsignedInteger, ValueKind::Message] => new UnsignedIntegerDivideDecimalNumberHandler();

        // DecimalNumber / float
        yield [ValueKind::Message, ValueKind::Float] => new DecimalNumberDivideFloatHandler();

        // float / DecimalNumber
        yield [ValueKind::Float, ValueKind::Message] => new FloatDivideDecimalNumberHandler();
    }
}
