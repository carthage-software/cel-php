<?php

declare(strict_types=1);

namespace Cel\Runtime\Value;

use Cel\Runtime\Exception\UnsupportedOperationException;
use Override;

/**
 * Represents a null value.
 */
final readonly class NullValue extends Value
{
    #[Override]
    public function getNativeValue(): null
    {
        return null;
    }

    #[Override]
    public function getKind(): ValueKind
    {
        return ValueKind::Null;
    }

    #[Override]
    public function isEqual(Value $other): bool
    {
        if (!$other instanceof NullValue) {
            throw UnsupportedOperationException::forEquality($this, $other);
        }

        return true;
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
