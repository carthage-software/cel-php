<?php

declare(strict_types=1);

namespace Cel\Function;

use Cel\Value\ValueKind;

/**
 * Defines the contract for a CEL function, which can contain multiple overloads.
 */
interface FunctionInterface
{
    /**
     * Returns the name of the function.
     *
     * @return non-empty-string
     */
    public function getName(): string;

    /**
     * Indicates whether the function is idempotent.
     *
     * An idempotent function produces the same result when called multiple times with the same arguments.
     * This property is crucial for optimizations like caching and query planning.
     *
     * @return bool True if the function is idempotent, false otherwise.
     */
    public function isIdempotent(): bool;

    /**
     * Returns an iterable of all overloads for this function.
     *
     * Each yielded value is a key-value pair:
     *
     * - Key: A `list<ValueKind>` representing the function signature.
     * - Value: A `FunctionOverloadHandlerInterface` that implements the logic for that signature.
     *
     * @return iterable<
     *      list<ValueKind>, // Function signature
     *      FunctionOverloadHandlerInterface // Function implementation handler
     * >
     */
    public function getOverloads(): iterable;
}
