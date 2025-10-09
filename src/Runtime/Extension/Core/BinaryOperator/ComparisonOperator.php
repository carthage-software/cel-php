<?php

declare(strict_types=1);

namespace Cel\Runtime\Extension\Core\BinaryOperator;

use Cel\Runtime\Operator\BinaryOperatorOverloadInterface;
use Cel\Runtime\Value\BooleanValue;
use Cel\Runtime\Value\Value;
use Cel\Runtime\Value\ValueKind;
use Cel\Syntax\Binary\BinaryOperatorKind;
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
        $comparableTypes = [
            ValueKind::Integer,
            ValueKind::UnsignedInteger,
            ValueKind::Float,
            ValueKind::String,
            ValueKind::Bytes,
            ValueKind::Boolean,
            ValueKind::Timestamp,
            ValueKind::Duration,
        ];

        $comparator = match ($this->operator) {
            BinaryOperatorKind::LessThan => static fn(Value $a, Value $b): bool => $a->isLessThan($b),
            BinaryOperatorKind::LessThanOrEqual => static fn(Value $a, Value $b): bool => (
                $a->isLessThan($b)
                || $a->isEqual($b)
            ),
            BinaryOperatorKind::GreaterThan => static fn(Value $a, Value $b): bool => $a->isGreaterThan($b),
            BinaryOperatorKind::GreaterThanOrEqual => static fn(Value $a, Value $b): bool => (
                $a->isGreaterThan($b)
                || $a->isEqual($b)
            ),
            default => throw new \LogicException('Invalid comparison operator'),
        };

        foreach ($comparableTypes as $type) {
            yield [$type, $type] => static fn(Value $left, Value $right): Value => new BooleanValue($comparator(
                $left,
                $right,
            ));
        }
    }
}
