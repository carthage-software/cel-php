<?php

declare(strict_types=1);

namespace Cel\Runtime;

use Cel\Runtime\Message\MessageInterface;

/**
 * Encapsulates the configuration for a CEL runtime environment.
 *
 * This class provides a way to control various features of the runtime,
 * such as which standard extensions are enabled and which message types
 * are allowed to be constructed, to ensure both flexibility and security.
 */
final readonly class Configuration
{
    /**
     * @param bool $enableMacros Whether to enable macro support (e.g., `has()`, `all()`).
     * @param bool $enableCoreExtension Whether to enable the core extension (e.g., `size()`, type conversions).
     * @param bool $enableDateTimeExtension Whether to enable the date/time extension (e.g., `timestamp()`, `duration()`).
     * @param bool $enableMathExtension Whether to enable the math extension (e.g., `min()`, `max()`).
     * @param bool $enableStringExtension Whether to enable the string extension (e.g., `contains()`, `startsWith()`).
     * @param bool $enableListExtension Whether to enable the list extension (e.g., `sort()`, `chunk()`).
     * @param list<class-string<MessageInterface>> $allowedMessageClasses A security-focused allowlist of message classes that can be constructed within an expression.
     *                                                                    Classes must implement `MessageInterface`. By default, no message construction is allowed.
     */
    public function __construct(
        public bool $enableMacros = true,
        public bool $enableCoreExtension = true,
        public bool $enableDateTimeExtension = true,
        public bool $enableMathExtension = true,
        public bool $enableStringExtension = true,
        public bool $enableListExtension = true,
        public array $allowedMessageClasses = [],
    ) {}

    /**
     * Creates a configuration that allows constructing only the specified message classes.
     *
     * This is a security-focused feature to prevent unauthorized message types
     * from being instantiated within CEL expressions.
     *
     * @param list<class-string<MessageInterface>> $allowedMessageClasses The list of allowed message classes.
     *
     * @return self The configuration instance with the specified allowed message classes.
     */
    public static function forAllowedMessages(array $allowedMessageClasses): self
    {
        return new self(allowedMessageClasses: $allowedMessageClasses);
    }
}
