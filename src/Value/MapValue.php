<?php

declare(strict_types=1);

namespace Cel\Value;

use Cel\Exception\UnsupportedOperationException;
use Cel\Util\MapKeyUtil;
use Override;

use function count;

/**
 * Represents a map value.
 *
 * @api
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
    public function isZeroValue(): bool
    {
        return [] === $this->value;
    }

    #[Override]
    public function isEqual(Value $other): bool
    {
        if (!$other instanceof MapValue) {
            return false;
        }

        if (count($this->value) !== count($other->value)) {
            return false;
        }

        foreach ($this->value as $key => $value) {
            $otherValue = $other->get($key);

            if (null === $otherValue || !$value->isEqual($otherValue)) {
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
    public function getRawValue(): array
    {
        $raw = [];
        foreach ($this->value as $key => $value) {
            $raw[MapKeyUtil::keyToRaw($key)] = $value->getRawValue();
        }

        return $raw;
    }
}
