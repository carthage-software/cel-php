<?php

declare(strict_types=1);

namespace Cel\Extension\Decimal\BinaryOperator;

use Cel\Extension\Decimal\BinaryOperator\Handler\Comparison\DecimalNumberCompareDecimalNumberHandler;
use Cel\Extension\Decimal\BinaryOperator\Handler\Comparison\DecimalNumberCompareFloatHandler;
use Cel\Extension\Decimal\BinaryOperator\Handler\Comparison\DecimalNumberCompareIntegerHandler;
use Cel\Extension\Decimal\BinaryOperator\Handler\Comparison\DecimalNumberCompareUnsignedIntegerHandler;
use Cel\Extension\Decimal\BinaryOperator\Handler\Comparison\FloatCompareDecimalNumberHandler;
use Cel\Extension\Decimal\BinaryOperator\Handler\Comparison\IntegerCompareDecimalNumberHandler;
use Cel\Extension\Decimal\BinaryOperator\Handler\Comparison\UnsignedIntegerCompareDecimalNumberHandler;
use Cel\Operator\BinaryOperatorOverloadInterface;
use Cel\Syntax\Binary\BinaryOperatorKind;
use Cel\Value\ValueKind;
use Override;

final readonly class ComparisonOperator implements BinaryOperatorOverloadInterface
{
    public function __construct(
        private BinaryOperatorKind $operator,
    ) {}

    #[Override]
    public function getOperator(): BinaryOperatorKind
    {
        return $this->operator;
    }

    #[Override]
    public function getOverloads(): iterable
    {
        // DecimalNumber <op> DecimalNumber
        yield [ValueKind::Message, ValueKind::Message] => new DecimalNumberCompareDecimalNumberHandler($this->operator);

        // DecimalNumber <op> int
        yield [ValueKind::Message, ValueKind::Integer] => new DecimalNumberCompareIntegerHandler($this->operator);

        // int <op> DecimalNumber
        yield [ValueKind::Integer, ValueKind::Message] => new IntegerCompareDecimalNumberHandler($this->operator);

        // DecimalNumber <op> uint
        yield [ValueKind::Message, ValueKind::UnsignedInteger] =>
            new DecimalNumberCompareUnsignedIntegerHandler($this->operator);

        // uint <op> DecimalNumber
        yield [ValueKind::UnsignedInteger, ValueKind::Message] =>
            new UnsignedIntegerCompareDecimalNumberHandler($this->operator);

        // DecimalNumber <op> float
        yield [ValueKind::Message, ValueKind::Float] => new DecimalNumberCompareFloatHandler($this->operator);

        // float <op> DecimalNumber
        yield [ValueKind::Float, ValueKind::Message] => new FloatCompareDecimalNumberHandler($this->operator);
    }
}
