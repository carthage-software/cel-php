<?php

declare(strict_types=1);

namespace Cel\Extension\Core\BinaryOperator;

use Cel\Extension\Core\BinaryOperator\Handler\DivisionOperator\FloatFloatHandler;
use Cel\Extension\Core\BinaryOperator\Handler\DivisionOperator\IntegerIntegerHandler;
use Cel\Extension\Core\BinaryOperator\Handler\DivisionOperator\UnsignedIntegerUnsignedIntegerHandler;
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
        yield [ValueKind::Integer, ValueKind::Integer] => new IntegerIntegerHandler();
        yield [ValueKind::UnsignedInteger, ValueKind::UnsignedInteger] => new UnsignedIntegerUnsignedIntegerHandler();
        yield [ValueKind::Float, ValueKind::Float] => new FloatFloatHandler();
    }
}
