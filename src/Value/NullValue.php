<?php

declare(strict_types=1);

namespace Cel\Value;

use Cel\Exception\UnsupportedOperationException;
use Override;

/**
 * Represents a null value.
 *
 * @api
 */
final readonly class NullValue extends Value
{
    #[Override]
    public function getRawValue(): null
    {
        return null;
    }

    #[Override]
    public function getKind(): ValueKind
    {
        return ValueKind::Null;
    }

    /**
     * @return non-empty-string
     */
    #[Override]
    public function getType(): string
    {
        return 'null_type';
    }

    #[Override]
    public function isZeroValue(): bool
    {
        return true;
    }

    #[Override]
    public function isEqual(Value $other): bool
    {
        return $other instanceof NullValue;
    }

    #[Override]
    public function isLessThan(Value $other): bool
    {
        throw UnsupportedOperationException::forComparison($this, $other);
    }

    #[Override]
    public function isGreaterThan(Value $other): bool
    {
        throw UnsupportedOperationException::forComparison($this, $other);
    }
}
