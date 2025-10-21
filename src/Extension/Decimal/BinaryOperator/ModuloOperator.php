<?php

declare(strict_types=1);

namespace Cel\Extension\Decimal\BinaryOperator;

use Cel\Extension\Decimal\BinaryOperator\Handler\Modulo\DecimalNumberModuloDecimalNumberHandler;
use Cel\Extension\Decimal\BinaryOperator\Handler\Modulo\DecimalNumberModuloFloatHandler;
use Cel\Extension\Decimal\BinaryOperator\Handler\Modulo\DecimalNumberModuloIntegerHandler;
use Cel\Extension\Decimal\BinaryOperator\Handler\Modulo\DecimalNumberModuloUnsignedIntegerHandler;
use Cel\Extension\Decimal\BinaryOperator\Handler\Modulo\FloatModuloDecimalNumberHandler;
use Cel\Extension\Decimal\BinaryOperator\Handler\Modulo\IntegerModuloDecimalNumberHandler;
use Cel\Extension\Decimal\BinaryOperator\Handler\Modulo\UnsignedIntegerModuloDecimalNumberHandler;
use Cel\Operator\BinaryOperatorOverloadInterface;
use Cel\Syntax\Binary\BinaryOperatorKind;
use Cel\Value\ValueKind;
use Override;

final readonly class ModuloOperator implements BinaryOperatorOverloadInterface
{
    #[Override]
    public function getOperator(): BinaryOperatorKind
    {
        return BinaryOperatorKind::Modulo;
    }

    #[Override]
    public function getOverloads(): iterable
    {
        // DecimalNumber % DecimalNumber
        yield [ValueKind::Message, ValueKind::Message] => new DecimalNumberModuloDecimalNumberHandler();

        // DecimalNumber % int
        yield [ValueKind::Message, ValueKind::Integer] => new DecimalNumberModuloIntegerHandler();

        // int % DecimalNumber
        yield [ValueKind::Integer, ValueKind::Message] => new IntegerModuloDecimalNumberHandler();

        // DecimalNumber % uint
        yield [ValueKind::Message, ValueKind::UnsignedInteger] => new DecimalNumberModuloUnsignedIntegerHandler();

        // uint % DecimalNumber
        yield [ValueKind::UnsignedInteger, ValueKind::Message] => new UnsignedIntegerModuloDecimalNumberHandler();

        // DecimalNumber % float
        yield [ValueKind::Message, ValueKind::Float] => new DecimalNumberModuloFloatHandler();

        // float % DecimalNumber
        yield [ValueKind::Float, ValueKind::Message] => new FloatModuloDecimalNumberHandler();
    }
}
