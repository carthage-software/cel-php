<?php

declare(strict_types=1);

namespace Cel\Value;

use Cel\Exception\UnsupportedOperationException;
use Override;
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

    /**
     * @return non-empty-string
     */
    #[Override]
    public function getType(): string
    {
        return 'google.protobuf.Duration';
    }

    #[Override]
    public function isZeroValue(): bool
    {
        return $this->value->isZero();
    }

    #[Override]
    public function isEqual(Value $other): bool
    {
        return $other instanceof DurationValue && 0 === $this->value->compare($other->value)->value;
    }

    #[Override]
    public function isGreaterThan(Value $other): bool
    {
        if (!$other instanceof DurationValue) {
            throw UnsupportedOperationException::forComparison($this, $other);
        }

        return $this->value->compare($other->value)->value > 0;
    }

    #[Override]
    public function isLessThan(Value $other): bool
    {
        if (!$other instanceof DurationValue) {
            throw UnsupportedOperationException::forComparison($this, $other);
        }

        return $this->value->compare($other->value)->value < 0;
    }

    #[Override]
    public function getRawValue(): Duration
    {
        return $this->value;
    }
}
