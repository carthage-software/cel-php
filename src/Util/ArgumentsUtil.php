<?php

declare(strict_types=1);

namespace Cel\Util;

use Cel\Exception\InternalException;
use Cel\Value\Value;
use Psl\Str;

final readonly class ArgumentsUtil
{
    private function __construct() {}

    /**
     * Gets the argument at the specified index and ensures it is of the expected type.
     *
     * @template T of Value
     *
     * @param array<int, Value> $arguments The list of arguments.
     * @param int<0, max> $index The index of the argument to retrieve.
     * @param class-string<T> $expectedType The expected class type of the argument.
     *
     * @return T The argument at the specified index, guaranteed to be of the expected type.
     *
     * @throws InternalException If the argument is missing or not of the expected type.
     */
    public static function get(array $arguments, int $index, string $expectedType): Value
    {
        $argument = $arguments[$index] ?? null;
        if (null === $argument) {
            throw InternalException::forMessage(Str\format('Argument at index %d is missing', $index));
        }

        if (!$argument instanceof $expectedType) {
            throw InternalException::forMessage(Str\format(
                'Argument at index %d is not of expected type %s, got %s',
                $index,
                $expectedType,
                $argument::class,
            ));
        }

        return $argument;
    }

    /**
     * Gets the optional argument at the specified index and ensures it is of the expected type.
     *
     * @template T of Value
     *
     * @param array<int, Value> $arguments The list of arguments.
     * @param int<0, max> $index The index of the argument to retrieve.
     * @param class-string<T> $expectedType The expected class type of the argument.
     *
     * @return null|T The argument at the specified index, guaranteed to be of the expected type, or null if not provided.
     *
     * @throws InternalException If the argument is not of the expected type.
     */
    public static function getOptional(array $arguments, int $index, string $expectedType): null|Value
    {
        $argument = $arguments[$index] ?? null;
        if (null === $argument) {
            return null;
        }

        if (!$argument instanceof $expectedType) {
            throw InternalException::forMessage(Str\format(
                'Argument at index %d is not of expected type %s, got %s',
                $index,
                $expectedType,
                $argument::class,
            ));
        }

        return $argument;
    }
}
