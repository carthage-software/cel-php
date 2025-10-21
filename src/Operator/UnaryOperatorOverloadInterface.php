<?php

declare(strict_types=1);

namespace Cel\Operator;

use Cel\Syntax\Unary\UnaryOperatorKind;
use Cel\Value\ValueKind;

/**
 * Defines the contract for a unary operator overload.
 */
interface UnaryOperatorOverloadInterface
{
    /**
     * Returns the unary operator kind this overload applies to.
     */
    public function getOperator(): UnaryOperatorKind;

    /**
     * Returns an iterable of all overloads for this unary operator.
     *
     * Each yielded value is a key-value pair:
     *
     * - Key: A `ValueKind` representing the operand type.
     * - Value: A `UnaryOperatorOverloadHandlerInterface` that implements the logic for that type.
     *
     * @return iterable<ValueKind, UnaryOperatorOverloadHandlerInterface>
     */
    public function getOverloads(): iterable;
}
