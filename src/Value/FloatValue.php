<?php

declare(strict_types=1);

namespace Cel\Value;

use Cel\Exception\UnsupportedOperationException;
use Override;

/**
 * Represents a float value.
 */
final readonly class FloatValue extends Value
{
    public function __construct(
        public float $value,
    ) {}

    #[Override]
    public function getKind(): ValueKind
    {
        return ValueKind::Float;
    }

    #[Override]
    public function isEqual(Value $other): bool
    {
        if ($other instanceof FloatValue) {
            return $this->value === $other->value;
        }

        if ($other instanceof IntegerValue || $other instanceof UnsignedIntegerValue) {
            return $this->value === (float) $other->getRawValue();
        }

        return false;
    }

    #[Override]
    public function isGreaterThan(Value $other): bool
    {
        if (!$other instanceof FloatValue) {
            throw UnsupportedOperationException::forComparison($this, $other);
        }

        return $this->value > $other->value;
    }

    #[Override]
    public function isLessThan(Value $other): bool
    {
        if (!$other instanceof FloatValue) {
            throw UnsupportedOperationException::forComparison($this, $other);
        }

        return $this->value < $other->value;
    }

    #[Override]
    public function getRawValue(): float
    {
        return $this->value;
    }
}
