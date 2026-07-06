<?php

declare(strict_types=1);

namespace Cel\Value;

use Cel\Exception\UnsupportedOperationException;
use Override;

use function array_map;
use function count;

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
    public function isZeroValue(): bool
    {
        return [] === $this->value;
    }

    #[Override]
    public function isEqual(Value $other): bool
    {
        if (!$other instanceof ListValue) {
            return false;
        }

        if (count($this->value) !== count($other->value)) {
            return false;
        }

        foreach ($this->value as $index => $item) {
            $otherItem = $other->value[$index] ?? null;
            if (null === $otherItem || !$item->isEqual($otherItem)) {
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
    public function getRawValue(): array
    {
        return array_map(static fn(Value $item): mixed => $item->getRawValue(), $this->value);
    }
}
