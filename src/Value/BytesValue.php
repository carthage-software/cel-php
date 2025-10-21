<?php

declare(strict_types=1);

namespace Cel\Value;

use Cel\Exception\UnsupportedOperationException;
use Override;

/**
 * Represents a bytes value.
 */
final readonly class BytesValue extends Value
{
    public function __construct(
        public string $value,
    ) {}

    #[Override]
    public function getKind(): ValueKind
    {
        return ValueKind::Bytes;
    }

    #[Override]
    public function isEqual(Value $other): bool
    {
        if (!$other instanceof BytesValue) {
            throw UnsupportedOperationException::forEquality($this, $other);
        }

        return $this->value === $other->value;
    }

    #[Override]
    public function isGreaterThan(Value $other): bool
    {
        if (!$other instanceof BytesValue) {
            throw UnsupportedOperationException::forComparison($this, $other);
        }

        return $this->value > $other->value;
    }

    #[Override]
    public function isLessThan(Value $other): bool
    {
        if (!$other instanceof BytesValue) {
            throw UnsupportedOperationException::forComparison($this, $other);
        }

        return $this->value < $other->value;
    }

    #[Override]
    public function getRawValue(): string
    {
        return $this->value;
    }
}
