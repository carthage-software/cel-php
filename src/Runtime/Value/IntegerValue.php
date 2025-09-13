<?php

declare(strict_types=1);

namespace Cel\Runtime\Value;

use Cel\Runtime\Exception\UnsupportedOperationException;
use Override;

/**
 * Represents an integer value.
 */
final readonly class IntegerValue extends Value
{
    public function __construct(
        public int $value,
    ) {}

    #[Override]
    public function getKind(): ValueKind
    {
        return ValueKind::Integer;
    }

    #[Override]
    public function isEqual(Value $other): bool
    {
        if ($other instanceof IntegerValue) {
            return $this->value === $other->value;
        }

        if ($other instanceof FloatValue) {
            return (float) $this->value === $other->value;
        }

        if ($other instanceof UnsignedIntegerValue) {
            // Compare as strings to avoid PHP's integer overflow issues with large unsigned integers
            return (string) $this->value === (string) $other->value;
        }

        return false;
    }

    #[Override]
    public function isGreaterThan(Value $other): bool
    {
        if (!$other instanceof IntegerValue) {
            throw UnsupportedOperationException::forComparison($this, $other);
        }

        return $this->value > $other->value;
    }

    #[Override]
    public function isLessThan(Value $other): bool
    {
        if (!$other instanceof IntegerValue) {
            throw UnsupportedOperationException::forComparison($this, $other);
        }

        return $this->value < $other->value;
    }

    #[Override]
    public function getNativeValue(): int
    {
        return $this->value;
    }

    /**
     * @return non-empty-string
     */
    #[Override]
    public function getType(): string
    {
        return 'int';
    }
}
