<?php

declare(strict_types=1);

namespace Cel\Util;

use Cel\Exception\InternalException;
use Cel\Value\Value;
use Psl\Str;

final readonly class OperandUtil
{
    private function __construct() {}

    /**
     * Assert the operand is of the expected type.
     *
     * This method is used to assert the type of operand in a unary operation overload handler.
     *
     * @template T of Value
     *
     * @param Value $value The value to check.
     * @param class-string<T> $expectedType The expected class type of the operand.
     *
     * @return T The operand, guaranteed to be of the expected type.
     *
     * @throws InternalException If the operand is not of the expected type.
     */
    public static function assert(Value $value, string $expectedType): Value
    {
        if (!$value instanceof $expectedType) {
            throw InternalException::forMessage(Str\format(
                'Operand is not of expected type %s, got %s',
                $expectedType,
                $value::class,
            ));
        }

        return $value;
    }

    /**
     * Assert the left operand is of the expected type.
     *
     * This method is used to assert the type of the left operand in a binary operation overload handler.
     *
     * @template T of Value
     *
     * @param Value $value The value to check.
     * @param class-string<T> $expectedType The expected class type of the operand.
     *
     * @return T The operand, guaranteed to be of the expected type.
     *
     * @throws InternalException If the operand is not of the expected type.
     */
    public static function assertLeft(Value $value, string $expectedType): Value
    {
        if (!$value instanceof $expectedType) {
            throw InternalException::forMessage(Str\format(
                'Left operand is not of expected type %s, got %s',
                $expectedType,
                $value::class,
            ));
        }

        return $value;
    }

    /**
     * Assert the right operand is of the expected type.
     *
     * This method is used to assert the type of the right operand in a binary operation overload handler.
     *
     * @template T of Value
     *
     * @param Value $value The value to check.
     * @param class-string<T> $expectedType The expected class type of the operand.
     *
     * @return T The operand, guaranteed to be of the expected type.
     *
     * @throws InternalException If the operand is not of the expected type.
     */
    public static function assertRight(Value $value, string $expectedType): Value
    {
        if (!$value instanceof $expectedType) {
            throw InternalException::forMessage(Str\format(
                'Right operand is not of expected type %s, got %s',
                $expectedType,
                $value::class,
            ));
        }

        return $value;
    }
}
