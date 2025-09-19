<?php

declare(strict_types=1);

namespace Cel\Runtime;

use Cel\Runtime\Exception\MisconfigurationException;
use Cel\Runtime\Message\MessageInterface;
use Psl\Iter;
use Psl\Str;

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
     * @var array<class-string<MessageInterface>, list<string>> A reverse mapping of message class names to their aliases for quick lookup.
     */
    public array $messageClassesToAliases;

    /**
     * @param bool $enableMacros Whether to enable macro support (e.g., `has()`, `all()`).
     * @param bool $enableCoreExtension Whether to enable the core extension (e.g., `size()`, type conversions).
     * @param bool $enableDateTimeExtension Whether to enable the date/time extension (e.g., `timestamp()`, `duration()`).
     * @param bool $enableMathExtension Whether to enable the math extension (e.g., `min()`, `max()`).
     * @param bool $enableStringExtension Whether to enable the string extension (e.g., `contains()`, `startsWith()`).
     * @param bool $enableListExtension Whether to enable the list extension (e.g., `sort()`, `chunk()`).
     * @param list<class-string<MessageInterface>> $allowedMessageClasses A security-focused allowlist of message classes that can be constructed within an expression.
     *                                                                    Classes must implement `MessageInterface`. By default, no message construction is allowed.
     * @param array<string, class-string<MessageInterface>> $messageClassAliases An optional mapping of custom type names to fully qualified message class names.
     *                                                                           This allows using shorter or more convenient names in expressions.
     * @param bool $enforceMessageClassAliases Whether to enforce the use of message class aliases when constructing messages.
     *                                         If true, a class in `$allowedMessageClasses` that also has an alias in `$messageClassAliases`
     *                                         can only be constructed using its alias.
     *
     * @throws MisconfigurationException if any alias in `$messageClassAliases` does not map to a class in `$allowedMessageClasses`.
     */
    public function __construct(
        public bool $enableMacros = true,
        public bool $enableCoreExtension = true,
        public bool $enableDateTimeExtension = true,
        public bool $enableMathExtension = true,
        public bool $enableStringExtension = true,
        public bool $enableListExtension = true,
        public array $allowedMessageClasses = [],
        public array $messageClassAliases = [],
        public bool $enforceMessageClassAliases = false,
    ) {
        foreach ($this->messageClassAliases as $messageAlias => $messageClassAlias) {
            if (!Iter\contains($this->allowedMessageClasses, $messageClassAlias)) {
                throw new MisconfigurationException(Str\format(
                    'Message class alias "%s" ( `%s` ) does not map to an allowed message class. '
                    . 'All aliases in $messageClassAliases must map to classes in $allowedMessageClasses.',
                    $messageAlias,
                    $messageClassAlias,
                ));
            }
        }

        /** @var array<class-string<MessageInterface>, list<string>> $messageClassesToAliases */
        $messageClassesToAliases = [];
        foreach ($this->messageClassAliases as $alias => $class) {
            $messageClassesToAliases[$class][] = $alias;
        }

        $this->messageClassesToAliases = $messageClassesToAliases;
    }

    /**
     * Creates a configuration that allows constructing only the specified message classes.
     *
     * This is a security-focused feature to prevent unauthorized message types
     * from being instantiated within CEL expressions.
     *
     * @param list<class-string<MessageInterface>> $allowedMessageClasses The list of allowed message classes.
     * @param array<string, class-string<MessageInterface>> $messageClassAliases An optional mapping of custom type names to fully qualified message class names.
     * @param bool $enforceMessageClassAliases Whether to enforce the use of message class aliases when constructing messages.
     *
     * @return self The configuration instance with the specified allowed message classes.
     *
     * @throws MisconfigurationException if any alias in `$messageClassAliases` does not map to a class in `$allowedMessageClasses`.
     */
    public static function forAllowedMessages(
        array $allowedMessageClasses,
        array $messageClassAliases = [],
        bool $enforceMessageClassAliases = false,
    ): self {
        return new self(
            allowedMessageClasses: $allowedMessageClasses,
            messageClassAliases: $messageClassAliases,
            enforceMessageClassAliases: $enforceMessageClassAliases,
        );
    }
}
