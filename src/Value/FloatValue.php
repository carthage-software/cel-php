<?php

declare(strict_types=1);

namespace Cel\Value;

use Cel\Exception\UnsupportedOperationException;
use Cel\Util\NumericComparator;
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
    public function isZeroValue(): bool
    {
        return 0.0 === $this->value;
    }

    #[Override]
    public function isEqual(Value $other): bool
    {
        if (!NumericComparator::isNumeric($other)) {
            return false;
        }

        return NumericComparator::equals($this, $other);
    }

    #[Override]
    public function isGreaterThan(Value $other): bool
    {
        if (!NumericComparator::isNumeric($other)) {
            throw UnsupportedOperationException::forComparison($this, $other);
        }

        return NumericComparator::order($this, $other) > 0;
    }

    #[Override]
    public function isLessThan(Value $other): bool
    {
        if (!NumericComparator::isNumeric($other)) {
            throw UnsupportedOperationException::forComparison($this, $other);
        }

        return NumericComparator::order($this, $other) < 0;
    }

    #[Override]
    public function getRawValue(): float
    {
        return $this->value;
    }
}
