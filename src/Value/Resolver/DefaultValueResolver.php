<?php

declare(strict_types=1);

namespace Cel\Value\Resolver;

use Cel\Exception\IncompatibleValueTypeException;
use Cel\Value\Value;
use Override;

/**
 * Default value resolver that uses Value::from() for standard PHP types.
 *
 * This resolver handles all built-in CEL types including:
 * - null
 * - bool
 * - int
 * - float
 * - string
 * - arrays (lists and maps)
 * - MessageInterface implementations
 */
final readonly class DefaultValueResolver implements ValueResolverInterface
{
    #[Override]
    public function canResolve(mixed $value): bool
    {
        // The default resolver can attempt to resolve any value
        // Value::from() will throw if it cannot be converted
        return true;
    }

    /**
     * @throws IncompatibleValueTypeException If the value cannot be converted to a CEL value.
     */
    #[Override]
    public function resolve(mixed $value): Value
    {
        return Value::from($value);
    }
}
