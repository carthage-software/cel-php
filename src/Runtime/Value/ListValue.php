<?php

declare(strict_types=1);

namespace Cel\Runtime\Value;

use Cel\Runtime\Exception\UnsupportedOperationException;
use Override;
use Psl\Iter;
use Psl\Vec;

/**
 * Represents a list value.
 */
final readonly class ListValue extends Value
{
    /**
     * @param list<Value> $value
     */
    public function __construct(
        public array $value,
    ) {}

    #[Override]
    public function getKind(): ValueKind
    {
        return ValueKind::List;
    }

    #[Override]
    public function isEqual(Value $other): bool
    {
        if (!$other instanceof ListValue) {
            throw UnsupportedOperationException::forEquality($this, $other);
        }

        if (Iter\count($this->value) !== Iter\count($other->value)) {
            return false;
        }

        foreach ($this->value as $index => $item) {
            $otherItem = $other->value[$index] ?? null;
            if ($otherItem === null || !$item->isEqual($otherItem)) {
                return false;
            }
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

    /**
     * @return list<mixed>
     */
    #[Override]
    public function getNativeValue(): array
    {
        return Vec\map($this->value, fn(Value $item): mixed => $item->getNativeValue());
    }
}
