<?php

declare(strict_types=1);

namespace Cel\Runtime\Extension\Core\BinaryOperator;

use Cel\Runtime\Operator\BinaryOperatorOverloadInterface;
use Cel\Runtime\Value\BooleanValue;
use Cel\Runtime\Value\Value;
use Cel\Runtime\Value\ValueKind;
use Cel\Syntax\Binary\BinaryOperatorKind;
use Override;

final readonly class LogicalAndOperator implements BinaryOperatorOverloadInterface
{
    #[Override]
    public function getOperator(): BinaryOperatorKind
    {
        return BinaryOperatorKind::And;
    }

    #[Override]
    public function getOverloads(): iterable
    {
        yield [ValueKind::Boolean, ValueKind::Boolean] =>
            /**
             * @param BooleanValue $left
             * @param BooleanValue $right
             */
            static fn(Value $left, Value $right): Value => new BooleanValue($left->value && $right->value);
    }
}
