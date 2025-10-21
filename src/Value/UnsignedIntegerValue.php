<?php

declare(strict_types=1);

namespace Cel\Value;

use Cel\Exception\UnsupportedOperationException;
use Override;

use function bccomp;

/**
 * Represents an unsigned integer value.
 */
final readonly class UnsignedIntegerValue extends Value
{
    public function __construct(
        public int|string $value,
    ) {}

    #[Override]
    public function getKind(): ValueKind
    {
        return ValueKind::UnsignedInteger;
    }

    #[Override]
    public function isEqual(Value $other): bool
    {
        if ($other instanceof UnsignedIntegerValue) {
            return bccomp((string) $this->value, (string) $other->value, 0) === 0;
        }

        if ($other instanceof IntegerValue) {
            return bccomp((string) $this->value, (string) $other->value, 0) === 0;
        }

        if ($other instanceof FloatValue) {
            return (float) $this->value === $other->value;
        }

        return false;
    }

    #[Override]
    public function isGreaterThan(Value $other): bool
    {
        if (!$other instanceof UnsignedIntegerValue) {
            throw UnsupportedOperationException::forComparison($this, $other);
        }

        return bccomp((string) $this->value, (string) $other->value, 0) === 1;
    }

    #[Override]
    public function isLessThan(Value $other): bool
    {
        if (!$other instanceof UnsignedIntegerValue) {
            throw UnsupportedOperationException::forComparison($this, $other);
        }

        return bccomp((string) $this->value, (string) $other->value, 0) === -1;
    }

    #[Override]
    public function getRawValue(): int|string
    {
        return $this->value;
    }
}
