<?php

declare(strict_types=1);

namespace Cel\Runtime\Extension\Core\BinaryOperator;

use Cel\Runtime\Operator\BinaryOperatorOverloadInterface;
use Cel\Runtime\Value\BooleanValue;
use Cel\Runtime\Value\Value;
use Cel\Runtime\Value\ValueKind;
use Cel\Syntax\Binary\BinaryOperatorKind;
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
        $allTypes = [
            ValueKind::Integer,
            ValueKind::UnsignedInteger,
            ValueKind::Float,
            ValueKind::String,
            ValueKind::Bytes,
            ValueKind::Boolean,
            ValueKind::Null,
            ValueKind::List,
            ValueKind::Map,
            ValueKind::Message,
            ValueKind::Timestamp,
            ValueKind::Duration,
        ];

        $isEqual = $this->operator === BinaryOperatorKind::Equal;

        foreach ($allTypes as $type) {
            yield [$type, $type] => static fn(Value $left, Value $right): Value => new BooleanValue(
                $isEqual ? $left->isEqual($right) : !$left->isEqual($right),
            );
        }
    }
}
