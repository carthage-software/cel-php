<?php

declare(strict_types=1);

namespace Cel\Extension\Decimal\UnaryOperator;

use Cel\Extension\Decimal\UnaryOperator\Handler\NegationOperator\DecimalNumberHandler;
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
        yield ValueKind::Message => new DecimalNumberHandler();
    }
}
