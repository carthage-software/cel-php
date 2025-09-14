<?php

declare(strict_types=1);

namespace Cel\Runtime\Value;

use Cel\Runtime\Exception\IncompatibleValueTypeException;
use Cel\Runtime\Exception\UnsupportedOperationException;
use Cel\Runtime\Message\MessageInterface;
use Psl\Dict;
use Psl\Str;
use Psl\Type;

use function gettype;
use function is_object;

/**
 * Represents a value in the CEL runtime.
 */
abstract readonly class Value
{
    /**
     * Returns the native PHP value.
     */
    abstract public function getNativeValue(): mixed;

    abstract public function getKind(): ValueKind;

    /**
     * Indicates whether this value is equal to another value.
     *
     * @param Value $other The other value to compare with.
     *
     * @return bool True if the values are equal, false otherwise.
     *
     * @throws UnsupportedOperationException If the values are not comparable.
     */
    abstract public function isEqual(Value $other): bool;

    /**
     * Indicates whether this value is less than another value.
     *
     * @param Value $other The other value to compare with.
     *
     * @return bool True if this value is less than the other value, false otherwise.
     *
     * @throws UnsupportedOperationException If the values are not comparable.
     */
    abstract public function isLessThan(Value $other): bool;

    /**
     * Indicates whether this value is greater than another value.
     *
     * @param Value $other The other value to compare with.
     *
     * @return bool True if this value is greater than the other value, false otherwise.
     *
     * @throws UnsupportedOperationException If the values are not comparable.
     */
    abstract public function isGreaterThan(Value $other): bool;

    /**
     * Returns the CEL type name of the value.
     *
     * @return non-empty-string
     */
    public function getType(): string
    {
        return $this->getKind()->value;
    }

    /**
     * Indicates whether this value is an aggregate type (list, map, or message).
     */
    public function isAggregate(): bool
    {
        return $this->getKind()->isAggregate();
    }

    /**
     * Creates a Value object from a native PHP value.
     *
     * @template V
     *
     * @param Value<V>|V $value
     *
     * @return Value<V>
     *
     * @throws IncompatibleValueTypeException If the value type is not supported.
     */
    public static function from(mixed $value): Value
    {
        if ($value instanceof Value) {
            return $value;
        }

        if (null === $value) {
            return new NullValue();
        }

        if (Type\bool()->matches($value)) {
            return new BooleanValue($value);
        }

        if (Type\float()->matches($value)) {
            return new FloatValue($value);
        }

        if (Type\int()->matches($value)) {
            return new IntegerValue($value);
        }

        if (Type\string()->matches($value)) {
            return new StringValue($value);
        }

        if (Type\mixed_dict()->matches($value)) {
            return self::fromArray($value);
        }

        if ($value instanceof MessageInterface) {
            return $value->toCelValue();
        }

        if (is_object($value)) {
            throw new IncompatibleValueTypeException(Str\format(
                'Incompatible object of class "%s", only classes implementing "%s" are supported',
                $value::class,
                MessageInterface::class,
            ));
        }

        throw new IncompatibleValueTypeException(Str\format('Incompatible PHP type "%s"', gettype($value)));
    }

    /**
     * @template K of array-key
     * @template V
     *
     * @param list<V>|array<K, V> $value
     *
     * @return ($value is list<V> ? ListValue : MapValue)
     */
    private static function fromArray(array $value): ListValue|MapValue
    {
        if (Type\mixed_vec()->matches($value)) {
            $items = [];
            foreach ($value as $item) {
                $items[] = self::from($item);
            }

            return new ListValue($items);
        }

        return new MapValue(Dict\map($value, Value::from(...)));
    }
}
