<?php

declare(strict_types=1);

namespace Cel\Value\Resolver;

use Cel\Value\Value;

/**
 * Converts raw PHP values into CEL Value instances.
 *
 * Value resolvers enable custom type support by allowing extensions
 * to provide their own conversion logic for specific types.
 */
interface ValueResolverInterface
{
    /**
     * Checks if this resolver can handle the given raw value.
     *
     * @param mixed $value The raw PHP value to check
     *
     * @return bool True if this resolver can convert the value, false otherwise
     */
    public function canResolve(mixed $value): bool;

    /**
     * Converts a raw PHP value into a CEL Value instance.
     *
     * @param mixed $value The raw PHP value to convert
     *
     * @return Value The converted CEL value
     *
     * @throws Exception\IncompatibleValueTypeException If the value cannot be converted
     */
    public function resolve(mixed $value): Value;
}
