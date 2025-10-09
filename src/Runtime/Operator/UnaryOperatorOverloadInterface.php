<?php

declare(strict_types=1);

namespace Cel\Runtime\Operator;

use Cel\Runtime\Value\Value;
use Cel\Runtime\Value\ValueKind;
use Cel\Syntax\Expression;
use Cel\Syntax\Unary\UnaryOperatorKind;

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
     * - Value: A `callable` that implements the logic for that type.
     *
     * @return iterable<ValueKind, (callable(Value, Expression): Value)>
     */
    public function getOverloads(): iterable;
}
