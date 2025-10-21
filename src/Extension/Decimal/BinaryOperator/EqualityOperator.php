<?php

declare(strict_types=1);

namespace Cel\Extension\Decimal\BinaryOperator;

use Cel\Extension\Decimal\BinaryOperator\Handler\Equality\DecimalNumberEqualsDecimalNumberHandler;
use Cel\Extension\Decimal\BinaryOperator\Handler\Equality\DecimalNumberEqualsFloatHandler;
use Cel\Extension\Decimal\BinaryOperator\Handler\Equality\DecimalNumberEqualsIntegerHandler;
use Cel\Extension\Decimal\BinaryOperator\Handler\Equality\DecimalNumberEqualsUnsignedIntegerHandler;
use Cel\Extension\Decimal\BinaryOperator\Handler\Equality\FloatEqualsDecimalNumberHandler;
use Cel\Extension\Decimal\BinaryOperator\Handler\Equality\IntegerEqualsDecimalNumberHandler;
use Cel\Extension\Decimal\BinaryOperator\Handler\Equality\UnsignedIntegerEqualsDecimalNumberHandler;
use Cel\Operator\BinaryOperatorOverloadInterface;
use Cel\Syntax\Binary\BinaryOperatorKind;
use Cel\Value\ValueKind;
use Override;

final readonly class EqualityOperator implements BinaryOperatorOverloadInterface
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
        // DecimalNumber == DecimalNumber
        yield [ValueKind::Message, ValueKind::Message] => new DecimalNumberEqualsDecimalNumberHandler($this->operator);

        // DecimalNumber == int
        yield [ValueKind::Message, ValueKind::Integer] => new DecimalNumberEqualsIntegerHandler($this->operator);

        // int == DecimalNumber
        yield [ValueKind::Integer, ValueKind::Message] => new IntegerEqualsDecimalNumberHandler($this->operator);

        // DecimalNumber == uint
        yield [ValueKind::Message, ValueKind::UnsignedInteger] =>
            new DecimalNumberEqualsUnsignedIntegerHandler($this->operator);

        // uint == DecimalNumber
        yield [ValueKind::UnsignedInteger, ValueKind::Message] =>
            new UnsignedIntegerEqualsDecimalNumberHandler($this->operator);

        // DecimalNumber == float
        yield [ValueKind::Message, ValueKind::Float] => new DecimalNumberEqualsFloatHandler($this->operator);

        // float == DecimalNumber
        yield [ValueKind::Float, ValueKind::Message] => new FloatEqualsDecimalNumberHandler($this->operator);
    }
}
