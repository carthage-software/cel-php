<?php

declare(strict_types=1);

namespace Cel\Runtime\Value;

use Cel\Runtime\Exception\UnsupportedOperationException;
use Override;

/**
 * Represents a string value.
 */
final readonly class StringValue extends Value
{
    public function __construct(
        public string $value,
    ) {}

    #[Override]
    public function getKind(): ValueKind
    {
        return ValueKind::String;
    }

    #[Override]
    public function isEqual(Value $other): bool
    {
        if (!$other instanceof StringValue) {
            throw UnsupportedOperationException::forEquality($this, $other);
        }

        return $this->value === $other->value;
    }

    #[Override]
    public function isGreaterThan(Value $other): bool
    {
        if (!$other instanceof StringValue) {
            throw UnsupportedOperationException::forComparison($this, $other);
        }

        return $this->value > $other->value;
    }

    #[Override]
    public function isLessThan(Value $other): bool
    {
        if (!$other instanceof StringValue) {
            throw UnsupportedOperationException::forComparison($this, $other);
        }

        return $this->value < $other->value;
    }

    #[Override]
    public function getNativeValue(): string
    {
        return $this->value;
    }
}
