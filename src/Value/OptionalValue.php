<?php

declare(strict_types=1);

namespace Cel\Value;

use Cel\Exception\UnsupportedOperationException;
use Override;

/**
 * Represents an optional value, which may or may not contain an inner value.
 *
 * Optional values are produced by optional field selection (`msg.?field`),
 * optional indexing (`map[?key]`, `list[?index]`), and by the `optional.of`,
 * `optional.ofNonZeroValue`, and `optional.none` functions. An optional either
 * holds a concrete value (`optional.of(x)`) or is empty (`optional.none()`).
 *
 * @api
 */
final readonly class OptionalValue extends Value
{
    /**
     * @param null|Value $value The wrapped value, or `null` when the optional is empty.
     */
    public function __construct(
        public null|Value $value = null,
    ) {}

    /**
     * Creates an optional containing the given value.
     */
    public static function of(Value $value): self
    {
        return new self($value);
    }

    /**
     * Creates an empty optional.
     */
    public static function none(): self
    {
        return new self(null);
    }

    /**
     * Indicates whether this optional contains a value.
     */
    public function hasValue(): bool
    {
        return null !== $this->value;
    }

    #[Override]
    public function getKind(): ValueKind
    {
        return ValueKind::Optional;
    }

    /**
     * An optional is never itself a zero value; `optional.ofNonZeroValue` always
     * wraps an optional operand.
     */
    #[Override]
    public function isZeroValue(): bool
    {
        return false;
    }

    #[Override]
    public function isEqual(Value $other): bool
    {
        if (!$other instanceof OptionalValue) {
            return false;
        }

        if (null === $this->value) {
            return null === $other->value;
        }

        if (null === $other->value) {
            return false;
        }

        return $this->value->isEqual($other->value);
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
     * Returns the raw value contained by the optional, or `null` when empty.
     */
    #[Override]
    public function getRawValue(): mixed
    {
        return $this->value?->getRawValue();
    }
}
