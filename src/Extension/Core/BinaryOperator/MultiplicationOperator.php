<?php

declare(strict_types=1);

namespace Cel\Extension\Core\BinaryOperator;

use Cel\Extension\Core\BinaryOperator\Handler\MultiplicationOperator\FloatFloatHandler;
use Cel\Extension\Core\BinaryOperator\Handler\MultiplicationOperator\IntegerIntegerHandler;
use Cel\Extension\Core\BinaryOperator\Handler\MultiplicationOperator\UnsignedIntegerUnsignedIntegerHandler;
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
        yield [ValueKind::Integer, ValueKind::Integer] => new IntegerIntegerHandler();
        yield [ValueKind::UnsignedInteger, ValueKind::UnsignedInteger] => new UnsignedIntegerUnsignedIntegerHandler();
        yield [ValueKind::Float, ValueKind::Float] => new FloatFloatHandler();
    }
}
