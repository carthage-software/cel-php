<?php

declare(strict_types=1);

namespace Cel\Runtime\Extension\Core\UnaryOperator;

use Cel\Runtime\Operator\UnaryOperatorOverloadInterface;
use Cel\Runtime\Value\BooleanValue;
use Cel\Runtime\Value\Value;
use Cel\Runtime\Value\ValueKind;
use Cel\Syntax\Unary\UnaryOperatorKind;
use Override;

final readonly class LogicalNotOperator implements UnaryOperatorOverloadInterface
{
    #[Override]
    public function getOperator(): UnaryOperatorKind
    {
        return UnaryOperatorKind::Not;
    }

    #[Override]
    public function getOverloads(): iterable
    {
        yield ValueKind::Boolean =>
            /**
             * @param BooleanValue $operand
             */
            static fn(Value $operand): Value => new BooleanValue(!$operand->value);
    }
}
