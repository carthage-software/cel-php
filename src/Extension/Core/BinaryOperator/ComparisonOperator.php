<?php

declare(strict_types=1);

namespace Cel\Extension\Core\BinaryOperator;

use Cel\Exception\InternalException;
use Cel\Extension\Core\BinaryOperator\Handler\ComparisonOperator\BooleanBooleanHandler;
use Cel\Extension\Core\BinaryOperator\Handler\ComparisonOperator\BytesBytesHandler;
use Cel\Extension\Core\BinaryOperator\Handler\ComparisonOperator\DurationDurationHandler;
use Cel\Extension\Core\BinaryOperator\Handler\ComparisonOperator\NumericHandler;
use Cel\Extension\Core\BinaryOperator\Handler\ComparisonOperator\StringStringHandler;
use Cel\Extension\Core\BinaryOperator\Handler\ComparisonOperator\TimestampTimestampHandler;
use Cel\Operator\BinaryOperatorOverloadInterface;
use Cel\Syntax\Binary\BinaryOperatorKind;
use Cel\Value\Value;
use Cel\Value\ValueKind;
use Override;

/**
 * @internal
 */
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
        $comparator = match ($this->operator) {
            BinaryOperatorKind::LessThan => static fn(Value $a, Value $b): bool => $a->isLessThan($b),
            BinaryOperatorKind::LessThanOrEqual => static fn(Value $a, Value $b): bool => (
                $a->isLessThan($b) || $a->isEqual($b)
            ),
            BinaryOperatorKind::GreaterThan => static fn(Value $a, Value $b): bool => $a->isGreaterThan($b),
            BinaryOperatorKind::GreaterThanOrEqual => static fn(Value $a, Value $b): bool => (
                $a->isGreaterThan($b) || $a->isEqual($b)
            ),
            default => throw InternalException::forInvalidOperator($this->operator->getSymbol()),
        };

        // Numeric comparisons work across int, uint, and double on a single number line.
        $numeric = new NumericHandler($comparator);
        $numericKinds = [ValueKind::Integer, ValueKind::UnsignedInteger, ValueKind::Float];
        foreach ($numericKinds as $left) {
            foreach ($numericKinds as $right) {
                yield [$left, $right] => $numeric;
            }
        }

        yield [ValueKind::String, ValueKind::String] => new StringStringHandler($comparator);
        yield [ValueKind::Bytes, ValueKind::Bytes] => new BytesBytesHandler($comparator);
        yield [ValueKind::Boolean, ValueKind::Boolean] => new BooleanBooleanHandler($comparator);
        yield [ValueKind::Timestamp, ValueKind::Timestamp] => new TimestampTimestampHandler($comparator);
        yield [ValueKind::Duration, ValueKind::Duration] => new DurationDurationHandler($comparator);
    }
}
