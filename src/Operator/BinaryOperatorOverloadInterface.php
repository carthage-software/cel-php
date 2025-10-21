<?php

declare(strict_types=1);

namespace Cel\Operator;

use Cel\Syntax\Binary\BinaryOperatorKind;
use Cel\Value\ValueKind;

/**
 * Defines the contract for a binary operator overload.
 */
interface BinaryOperatorOverloadInterface
{
    /**
     * Returns the binary operator kind this overload applies to.
     */
    public function getOperator(): BinaryOperatorKind;

    /**
     * Returns an iterable of all overloads for this binary operator.
     *
     * Each yielded value is a key-value pair:
     *
     * - Key: A `list{ValueKind, ValueKind}` representing [LHS type, RHS type].
     * - Value: A `BinaryOperatorOverloadHandlerInterface` that implements the logic for that type combination.
     *
     * @return iterable<list{ValueKind, ValueKind}, BinaryOperatorOverloadHandlerInterface>
     */
    public function getOverloads(): iterable;
}
