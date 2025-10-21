<?php

declare(strict_types=1);

namespace Cel\Value;

use Cel\Exception\UnsupportedOperationException;
use Override;
use Psl\Comparison\Order;
use Psl\DateTime\Duration;

/**
 * Represents a timestamp value.
 */
final readonly class DurationValue extends Value
{
    public function __construct(
        public Duration $value,
    ) {}

    #[Override]
    public function getKind(): ValueKind
    {
        return ValueKind::Duration;
    }

    #[Override]
    public function isEqual(Value $other): bool
    {
        if (!$other instanceof DurationValue) {
            throw UnsupportedOperationException::forEquality($this, $other);
        }

        return $this->value->compare($other->value) === Order::Equal;
    }

    #[Override]
    public function isGreaterThan(Value $other): bool
    {
        if (!$other instanceof DurationValue) {
            throw UnsupportedOperationException::forComparison($this, $other);
        }

        return $this->value->compare($other->value) === Order::Greater;
    }

    #[Override]
    public function isLessThan(Value $other): bool
    {
        if (!$other instanceof DurationValue) {
            throw UnsupportedOperationException::forComparison($this, $other);
        }

        return $this->value->compare($other->value) === Order::Less;
    }

    #[Override]
    public function getRawValue(): Duration
    {
        return $this->value;
    }
}
