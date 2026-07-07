<?php

declare(strict_types=1);

namespace Cel\Value;

use Cel\Exception\UnsupportedOperationException;
use Override;
use Psl\DateTime\Timestamp;

/**
 * Represents a timestamp value.
 *
 * @api
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
     * @return non-empty-string
     */
    #[Override]
    public function getType(): string
    {
        return 'google.protobuf.Timestamp';
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
        return $other instanceof TimestampValue && 0 === $this->value->compare($other->value)->value;
    }

    #[Override]
    public function isGreaterThan(Value $other): bool
    {
        if (!$other instanceof TimestampValue) {
            throw UnsupportedOperationException::forComparison($this, $other);
        }

        return $this->value->compare($other->value)->value > 0;
    }

    #[Override]
    public function isLessThan(Value $other): bool
    {
        if (!$other instanceof TimestampValue) {
            throw UnsupportedOperationException::forComparison($this, $other);
        }

        return $this->value->compare($other->value)->value < 0;
    }

    #[Override]
    public function getRawValue(): Timestamp
    {
        return $this->value;
    }
}
