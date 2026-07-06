<?php

declare(strict_types=1);

namespace Cel\Value;

use Cel\Exception\UnsupportedOperationException;
use Override;

/**
 * Represents a CEL type as a first-class value, produced by the `type` function
 * and by bare type denotations such as `int` or `string`.
 *
 * Two type values are equal when they name the same type. The type of a type
 * value is itself `type`, so `type(type(x))` yields `type`.
 */
final readonly class TypeValue extends Value
{
    /**
     * The bare identifiers that denote a type (e.g. writing `int` on its own).
     *
     * @var list<non-empty-string>
     */
    private const array DENOTATIONS = [
        'bool',
        'bytes',
        'double',
        'int',
        'uint',
        'string',
        'list',
        'map',
        'null_type',
        'type',
        'optional_type',
        // Well-known types that resolve to a runtime type value (unlike the
        // wrapper types, which are not usable as bare type references).
        'google.protobuf.Timestamp',
        'google.protobuf.Duration',
    ];

    /**
     * @param non-empty-string $name The CEL type name (e.g. `int`, `double`, `type`).
     */
    public function __construct(
        public string $name,
    ) {}

    /**
     * Resolves a bare identifier to the type value it denotes, or null when the
     * identifier is not a type name.
     */
    public static function denotation(string $name): null|self
    {
        foreach (self::DENOTATIONS as $denotation) {
            if ($denotation === $name) {
                return new self($denotation);
            }
        }

        return null;
    }

    #[Override]
    public function getKind(): ValueKind
    {
        return ValueKind::Type;
    }

    /**
     * A type is never a zero value.
     */
    #[Override]
    public function isZeroValue(): bool
    {
        return false;
    }

    #[Override]
    public function isEqual(Value $other): bool
    {
        return $other instanceof TypeValue && $this->name === $other->name;
    }

    #[Override]
    public function isGreaterThan(Value $other): bool
    {
        throw UnsupportedOperationException::forComparison($this, $other);
    }

    #[Override]
    public function isLessThan(Value $other): bool
    {
        throw UnsupportedOperationException::forComparison($this, $other);
    }

    /**
     * @return non-empty-string
     */
    #[Override]
    public function getType(): string
    {
        return 'type';
    }

    /**
     * @return non-empty-string
     */
    #[Override]
    public function getRawValue(): string
    {
        return $this->name;
    }
}
