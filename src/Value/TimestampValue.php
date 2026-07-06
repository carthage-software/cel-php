<?php

declare(strict_types=1);

namespace Cel\Value;

use Cel\Exception\UnsupportedOperationException;
use Override;
use Psl\Comparison\Order;
use Psl\DateTime\Timestamp;

/**
 * Represents a timestamp value.
 */
final readonly class TimestampValue extends Value
{
    public function __construct(
        public Timestamp $value,
    ) {}

    #[Override]
    public function getKind(): ValueKind
    {
        return ValueKind::Timestamp;
    }

    /**
     * A timestamp is never a zero value: the CEL/Go zero timestamp is the
     * uninitialized zero time sentinel, which cannot be represented here (even
     * the Unix epoch is explicitly not a zero value).
     */
    #[Override]
    public function isZeroValue(): bool
    {
        return false;
    }

    #[Override]
    public function isEqual(Value $other): bool
    {
        return $other instanceof TimestampValue && $this->value->compare($other->value) === Order::Equal;
    }

    #[Override]
    public function isGreaterThan(Value $other): bool
    {
        if (!$other instanceof TimestampValue) {
            throw UnsupportedOperationException::forComparison($this, $other);
        }

        return $this->value->compare($other->value) === Order::Greater;
    }

    #[Override]
    public function isLessThan(Value $other): bool
    {
        if (!$other instanceof TimestampValue) {
            throw UnsupportedOperationException::forComparison($this, $other);
        }

        return $this->value->compare($other->value) === Order::Less;
    }

    #[Override]
    public function getRawValue(): Timestamp
    {
        return $this->value;
    }
}
