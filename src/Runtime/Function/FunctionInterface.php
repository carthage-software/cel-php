<?php

declare(strict_types=1);

namespace Cel\Runtime\Function;

use Cel\Runtime\Value\Value;
use Cel\Runtime\Value\ValueKind;
use Cel\Syntax\Member\CallExpression;

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
     * Returns an iterable of all overloads for this function.
     *
     * Each yielded value is a key-value pair:
     *
     * - Key: A `list<ValueKind>` representing the function signature.
     * - Value: A `callable` that implements the logic for that signature.
     *
     * @return iterable<
     *      list<ValueKind>, // Function signature
     *      (callable(CallExpression, list<Value>): Value) // Function implementation
     * >
     */
    public function getOverloads(): iterable;
}
