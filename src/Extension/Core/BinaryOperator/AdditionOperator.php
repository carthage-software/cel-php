<?php

declare(strict_types=1);

namespace Cel\Extension\Core\BinaryOperator;

use Cel\Extension\Core\BinaryOperator\Handler\AdditionOperator\BytesBytesHandler;
use Cel\Extension\Core\BinaryOperator\Handler\AdditionOperator\FloatFloatHandler;
use Cel\Extension\Core\BinaryOperator\Handler\AdditionOperator\IntegerIntegerHandler;
use Cel\Extension\Core\BinaryOperator\Handler\AdditionOperator\ListListHandler;
use Cel\Extension\Core\BinaryOperator\Handler\AdditionOperator\StringStringHandler;
use Cel\Extension\Core\BinaryOperator\Handler\AdditionOperator\UnsignedIntegerUnsignedIntegerHandler;
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
        yield [ValueKind::Integer, ValueKind::Integer] => new IntegerIntegerHandler();
        yield [ValueKind::UnsignedInteger, ValueKind::UnsignedInteger] => new UnsignedIntegerUnsignedIntegerHandler();
        yield [ValueKind::Float, ValueKind::Float] => new FloatFloatHandler();
        yield [ValueKind::String, ValueKind::String] => new StringStringHandler();
        yield [ValueKind::Bytes, ValueKind::Bytes] => new BytesBytesHandler();
        yield [ValueKind::List, ValueKind::List] => new ListListHandler();
    }
}
