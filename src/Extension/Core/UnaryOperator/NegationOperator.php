<?php

declare(strict_types=1);

namespace Cel\Extension\Core\UnaryOperator;

use Cel\Extension\Core\UnaryOperator\Handler\NegationOperator\FloatHandler;
use Cel\Extension\Core\UnaryOperator\Handler\NegationOperator\IntegerHandler;
use Cel\Operator\UnaryOperatorOverloadInterface;
use Cel\Syntax\Unary\UnaryOperatorKind;
use Cel\Value\ValueKind;
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
        yield ValueKind::Integer => new IntegerHandler();
        yield ValueKind::Float => new FloatHandler();
    }
}
