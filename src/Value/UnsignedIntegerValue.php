<?php

declare(strict_types=1);

namespace Cel\Value;

use Cel\Exception\UnsupportedOperationException;
use Cel\Util\NumericComparator;
use Override;

use function bccomp;

/**
 * Represents an unsigned integer value.
 *
 * @api
 */
final readonly class UnsignedIntegerValue extends Value
{
    /**
     * @param int|numeric-string $value The unsigned integer value, which can be a native int or a numeric string for large values.
     */
    public function __construct(
        public int|string $value,
    ) {}

    #[Override]
    public function getKind(): ValueKind
    {
        return ValueKind::UnsignedInteger;
    }

    #[Override]
    public function isZeroValue(): bool
    {
        return 0 === bccomp((string) $this->value, '0', 0);
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

    /**
     * @return int|numeric-string The raw unsigned integer value, which can be a native int or a numeric string for large values.
     */
    #[Override]
    public function getRawValue(): int|string
    {
        return $this->value;
    }
}
