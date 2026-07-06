<?php

declare(strict_types=1);

namespace Cel\Value;

use Cel\Exception\IncompatibleValueTypeException;
use Cel\Exception\UnsupportedOperationException;
use Cel\Message\MessageInterface;
use Cel\Util\MapKeyUtil;

use function array_is_list;
use function gettype;
use function is_array;
use function is_bool;
use function is_float;
use function is_int;
use function is_object;
use function is_string;
use function sprintf;

/**
 * Represents a value in the CEL runtime.
 */
abstract readonly class Value
{
    /**
     * Returns the raw PHP value.
     */
    abstract public function getRawValue(): mixed;

    abstract public function getKind(): ValueKind;

    /**
     * Indicates whether this value is a "zero value": the default empty value
     * for its type (e.g. `false`, `0`, `""`, `b""`, `[]`, `{}`, or `null`).
     *
     * Used by `optional.ofNonZeroValue` to decide whether a value should be
     * wrapped or treated as absent.
     */
    abstract public function isZeroValue(): bool;

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

        if (is_bool($value)) {
            return new BooleanValue($value);
        }

        if (is_float($value)) {
            return new FloatValue($value);
        }

        if (is_int($value)) {
            return new IntegerValue($value);
        }

        if (is_string($value)) {
            return new StringValue($value);
        }

        if (is_array($value)) {
            return self::fromArray($value);
        }

        if ($value instanceof MessageInterface) {
            return $value->toCelValue();
        }

        if (is_object($value)) {
            throw new IncompatibleValueTypeException(sprintf(
                'Incompatible object of class "%s", only classes implementing "%s" are supported',
                $value::class,
                MessageInterface::class,
            ));
        }

        throw new IncompatibleValueTypeException(sprintf('Incompatible PHP type "%s"', gettype($value)));
    }

    /**
     * @template K of array-key
     * @template V
     *
     * @param list<V>|array<K, V> $value
     *
     * @return ($value is list<V> ? ListValue : MapValue)
     *
     * @throws IncompatibleValueTypeException If array contains unsupported value types.
     */
    private static function fromArray(array $value): ListValue|MapValue
    {
        if (array_is_list($value)) {
            $items = [];
            foreach ($value as $item) {
                $items[] = self::from($item);
            }

            return new ListValue($items);
        }

        // Encode the native keys the same way the interpreter does, so a map
        // built from a PHP array is addressable by CEL expressions.
        $entries = [];
        foreach ($value as $key => $item) {
            $keyValue = is_int($key) ? new IntegerValue($key) : new StringValue($key);
            $entries[(string) MapKeyUtil::resolve($keyValue)] = self::from($item);
        }

        return new MapValue($entries);
    }
}
