<?php

declare(strict_types=1);

namespace Cel\Runtime\Message;

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
     * Creates a new instance of the class from a CEL `Value`.
     *
     * This method is called by the runtime when `getNativeValue()` is invoked
     * on a `MessageValue` that represents this class.
     *
     * @param Value $value The CEL value to convert from.
     *
     * @return static The new PHP object instance.
     */
    public static function fromCelValue(Value $value): static;
}
