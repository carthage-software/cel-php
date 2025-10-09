<?php

declare(strict_types=1);

namespace Cel\Runtime\Extension\Core\UnaryOperator;

use Cel\Runtime\Operator\UnaryOperatorOverloadInterface;
use Cel\Runtime\Value\FloatValue;
use Cel\Runtime\Value\IntegerValue;
use Cel\Runtime\Value\Value;
use Cel\Runtime\Value\ValueKind;
use Cel\Syntax\Unary\UnaryOperatorKind;
use Override;

final readonly class NegationOperator implements UnaryOperatorOverloadInterface
{
    #[Override]
    public function getOperator(): UnaryOperatorKind
    {
        return UnaryOperatorKind::Negate;
    }

    #[Override]
    public function getOverloads(): iterable
    {
        yield ValueKind::Integer =>
            /**
             * @param IntegerValue $operand
             */
            static fn(Value $operand): Value => new IntegerValue(-$operand->value);

        yield ValueKind::Float =>
            /**
             * @param FloatValue $operand
             */
            static fn(Value $operand): Value => new FloatValue(-$operand->value);
    }
}
