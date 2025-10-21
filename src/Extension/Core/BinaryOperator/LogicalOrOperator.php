<?php

declare(strict_types=1);

namespace Cel\Extension\Core\BinaryOperator;

use Cel\Extension\Core\BinaryOperator\Handler\LogicalOrOperator\BooleanBooleanHandler;
use Cel\Operator\BinaryOperatorOverloadInterface;
use Cel\Syntax\Binary\BinaryOperatorKind;
use Cel\Value\ValueKind;
use Override;

final readonly class LogicalOrOperator implements BinaryOperatorOverloadInterface
{
    #[Override]
    public function getOperator(): BinaryOperatorKind
    {
        return BinaryOperatorKind::Or;
    }

    #[Override]
    public function getOverloads(): iterable
    {
        yield [ValueKind::Boolean, ValueKind::Boolean] => new BooleanBooleanHandler();
    }
}
