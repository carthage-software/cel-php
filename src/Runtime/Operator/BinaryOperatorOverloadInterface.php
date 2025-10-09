<?php

declare(strict_types=1);

namespace Cel\Runtime\Operator;

use Cel\Runtime\Value\Value;
use Cel\Runtime\Value\ValueKind;
use Cel\Syntax\Binary\BinaryOperatorKind;
use Cel\Syntax\Expression;

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
     * - Value: A `callable` that implements the logic for that type combination.
     *
     * @return iterable<list{ValueKind, ValueKind}, (callable(Value, Value, Expression, Expression): Value)>
     */
    public function getOverloads(): iterable;
}
