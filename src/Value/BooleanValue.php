<?php

declare(strict_types=1);

namespace Cel\Value;

use Cel\Exception\UnsupportedOperationException;
use Override;

/**
 * Represents a boolean value.
 */
final readonly class BooleanValue extends Value
{
    public function __construct(
        public bool $value,
    ) {}

    #[Override]
    public function getKind(): ValueKind
    {
        return ValueKind::Boolean;
    }

    #[Override]
    public function isEqual(Value $other): bool
    {
        if (!$other instanceof BooleanValue) {
            throw UnsupportedOperationException::forEquality($this, $other);
        }

        return $this->value === $other->value;
    }

    #[Override]
    public function isGreaterThan(Value $other): bool
    {
        if (!$other instanceof BooleanValue) {
            throw UnsupportedOperationException::forComparison($this, $other);
        }

        return (int) $this->value > (int) $other->value;
    }

    #[Override]
    public function isLessThan(Value $other): bool
    {
        if (!$other instanceof BooleanValue) {
            throw UnsupportedOperationException::forComparison($this, $other);
        }

        return (int) $this->value < (int) $other->value;
    }

    #[Override]
    public function getRawValue(): bool
    {
        return $this->value;
    }
}
