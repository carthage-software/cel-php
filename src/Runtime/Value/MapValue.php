<?php

declare(strict_types=1);

namespace Cel\Runtime\Value;

use Cel\Runtime\Exception\UnsupportedOperationException;
use Override;
use Psl\Dict;
use Psl\Iter;
use Psl\Vec;

use function array_map;
use function count;

/**
 * Represents a map value.
 */
final readonly class MapValue extends Value
{
    /**
     * @param array<array-key, Value> $value
     */
    public function __construct(
        public array $value,
    ) {}

    #[Override]
    public function getKind(): ValueKind
    {
        return ValueKind::Map;
    }

    #[Override]
    public function isEqual(Value $other): bool
    {
        if (!$other instanceof MapValue) {
            throw UnsupportedOperationException::forEquality($this, $other);
        }

        if (Iter\count($this->value) !== Iter\count($other->value)) {
            return false;
        }

        foreach ($this->value as $key => $value) {
            $otherValue = $other->get($key);

            if ($otherValue === null || !$value->isEqual($otherValue)) {
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
     * Check if the map has the specified key.
     */
    public function has(string|int $key): bool
    {
        return isset($this->value[$key]);
    }

    /**
     * Get a map entry by key.
     *
     * @param array-key $key
     */
    public function get(string|int $key): Value|null
    {
        return $this->value[$key] ?? null;
    }

    /**
     * @return array<array-key, mixed>
     */
    #[Override]
    public function getNativeValue(): array
    {
        return Dict\map($this->value, static fn(Value $value): mixed => $value->getNativeValue());
    }
}
