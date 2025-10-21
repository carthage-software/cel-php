<?php

declare(strict_types=1);

namespace Cel\Extension\Core\BinaryOperator;

use Cel\Extension\Core\BinaryOperator\Handler\SubtractionOperator\FloatFloatHandler;
use Cel\Extension\Core\BinaryOperator\Handler\SubtractionOperator\IntegerIntegerHandler;
use Cel\Extension\Core\BinaryOperator\Handler\SubtractionOperator\UnsignedIntegerUnsignedIntegerHandler;
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
        yield [ValueKind::Integer, ValueKind::Integer] => new IntegerIntegerHandler();
        yield [ValueKind::UnsignedInteger, ValueKind::UnsignedInteger] => new UnsignedIntegerUnsignedIntegerHandler();
        yield [ValueKind::Float, ValueKind::Float] => new FloatFloatHandler();
    }
}
