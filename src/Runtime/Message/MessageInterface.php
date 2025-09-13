<?php

declare(strict_types=1);

namespace Cel\Runtime\Message;

use Cel\Runtime\Exception\IncompatibleValueTypeException;
use Cel\Runtime\Exception\InvalidMessageFieldsException;
use Cel\Runtime\Value\Value;

/**
 * Defines the contract for a PHP class that can be seamlessly converted
 * to and from a CEL runtime value.
 *
 * Implementing this interface allows user-defined classes to be treated as
 * first-class message types within the CEL runtime, enabling them to be
 * passed into evaluations and returned from custom functions.
 */
interface MessageInterface
{
    /**
     * Converts the current object instance into its corresponding CEL `Value` representation.
     *
     * This method is called by the runtime when a PHP object implementing this
     * interface is passed as a variable to `Value::from()`.
     *
     * @return Value<static>
     */
    public function toCelValue(): Value;

    /**
     * Creates a new instance of the class from an associative array of field values.
     *
     * This method is called by the runtime when constructing a message
     * from a message literal in CEL syntax.
     *
     * @param array<string, Value> $fields The associative array of field names to CEL values.
     *
     * @return static The new PHP object instance.
     *
     * @throws InvalidMessageFieldsException If any field value cannot be converted to the expected PHP type,
     *                                       or if a required field is missing, or if there are unknown fields.
     */
    public static function fromCelFields(array $fields): static;
}
