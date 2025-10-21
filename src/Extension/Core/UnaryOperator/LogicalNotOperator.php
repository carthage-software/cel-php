<?php

declare(strict_types=1);

namespace Cel\Extension\Core\UnaryOperator;

use Cel\Extension\Core\UnaryOperator\Handler\LogicalNotOperator\BooleanHandler;
use Cel\Operator\UnaryOperatorOverloadInterface;
use Cel\Syntax\Unary\UnaryOperatorKind;
use Cel\Value\ValueKind;
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
        yield ValueKind::Boolean => new BooleanHandler();
    }
}
