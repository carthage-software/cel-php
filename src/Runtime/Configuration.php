<?php

declare(strict_types=1);

namespace Cel\Runtime;

use Cel\Exception\MisconfigurationException;
use Cel\Extension\Core\CoreExtension;
use Cel\Extension\DateTime\DateTimeExtension;
use Cel\Extension\ExtensionInterface;
use Cel\Extension\List\ListExtension;
use Cel\Extension\Math\MathExtension;
use Cel\Extension\String\StringExtension;
use Cel\Interpreter\Macro\AllMacro;
use Cel\Interpreter\Macro\ExistsMacro;
use Cel\Interpreter\Macro\ExistsOneMacro;
use Cel\Interpreter\Macro\FilterMacro;
use Cel\Interpreter\Macro\HasMacro;
use Cel\Interpreter\Macro\MacroRegistry;
use Cel\Interpreter\Macro\MapMacro;
use Cel\Message\MessageInterface;
use Cel\Value\Resolver\ValueResolverInterface;
use Override;
use Psl\Default\DefaultInterface;
use Psl\Iter;
use Psl\Str;

/**
 * Encapsulates the configuration for a CEL runtime environment.
 *
 * This class provides a way to control various features of the runtime,
 * such as which extensions are enabled and which message types
 * are allowed to be constructed, to ensure both flexibility and security.
 */
final class Configuration implements DefaultInterface
{
    /**
     * @var array<class-string<MessageInterface>, list<string>> A reverse mapping of message class names to their aliases for quick lookup.
     */
    public array $messageClassesToAliases;

    /**
     * @var list<ExtensionInterface>
     */
    private array $extensions = [];

    private readonly MacroRegistry $macroRegistry;

    /**
     * @param bool $enableMacros Whether to enable macro support (e.g., `has()`, `all()`).
     * @param list<class-string<MessageInterface>> $allowedMessageClasses A security-focused allowlist of message classes that can be constructed within an expression.
     *                                                                    Classes must implement `MessageInterface`. By default, no message construction is allowed.
     * @param array<string, class-string<MessageInterface>> $messageClassAliases An optional mapping of custom type names to fully qualified message class names.
     *                                                                           This allows using shorter or more convenient names in expressions.
     * @param bool $enforceMessageClassAliases Whether to enforce the use of message class aliases when constructing messages.
     *                                         If true, a class in `$allowedMessageClasses` that also has an alias in `$messageClassAliases`
     *                                         can only be constructed using its alias.
     * @param bool $enableStandardExtensions Whether to enable all standard extensions (core, datetime, math, string, list).
     *
     * @throws MisconfigurationException if any alias in `$messageClassAliases` does not map to a class in `$allowedMessageClasses`.
     */
    public function __construct(
        public bool $enableMacros = true,
        public array $allowedMessageClasses = [],
        public array $messageClassAliases = [],
        public bool $enforceMessageClassAliases = false,
        bool $enableStandardExtensions = true,
    ) {
        foreach ($this->messageClassAliases as $messageAlias => $messageClassAlias) {
            if (Iter\contains($this->allowedMessageClasses, $messageClassAlias)) {
                continue;
            }

            throw new MisconfigurationException(Str\format(
                'Message class alias "%s" ( `%s` ) does not map to an allowed message class. '
                . 'All aliases in $messageClassAliases must map to classes in $allowedMessageClasses.',
                $messageAlias,
                $messageClassAlias,
            ));
        }

        /** @var array<class-string<MessageInterface>, list<string>> $messageClassesToAliases */
        $messageClassesToAliases = [];
        foreach ($this->messageClassAliases as $alias => $class) {
            $messageClassesToAliases[$class][] = $alias;
        }

        $this->messageClassesToAliases = $messageClassesToAliases;

        // Initialize macro registry
        $this->macroRegistry = new MacroRegistry();
        if ($enableMacros) {
            $this->macroRegistry->register(new HasMacro());
            $this->macroRegistry->register(new AllMacro());
            $this->macroRegistry->register(new ExistsMacro());
            $this->macroRegistry->register(new ExistsOneMacro());
            $this->macroRegistry->register(new FilterMacro());
            $this->macroRegistry->register(new MapMacro());
        }

        // Register standard extensions by default
        if ($enableStandardExtensions) {
            $this->addExtension(new CoreExtension());
            $this->addExtension(new DateTimeExtension());
            $this->addExtension(new MathExtension());
            $this->addExtension(new StringExtension());
            $this->addExtension(new ListExtension());
        }
    }

    /**
     * Adds an extension to the configuration.
     *
     * @param ExtensionInterface $extension The extension to add
     */
    public function addExtension(ExtensionInterface $extension): void
    {
        $this->extensions[] = $extension;
    }

    /**
     * Gets all registered extensions.
     *
     * @return list<ExtensionInterface>
     */
    public function getExtensions(): array
    {
        return $this->extensions;
    }

    /**
     * Gets all value resolvers from registered extensions.
     *
     * @return list<ValueResolverInterface>
     */
    public function getValueResolvers(): array
    {
        $resolvers = [];
        foreach ($this->extensions as $extension) {
            $resolvers = [...$resolvers, ...$extension->getValueResolvers()];
        }
        return $resolvers;
    }

    /**
     * Gets the macro registry.
     */
    public function getMacroRegistry(): MacroRegistry
    {
        return $this->macroRegistry;
    }

    /**
     * Creates a default configuration instance with standard settings.
     *
     * @return static
     */
    #[Override]
    public static function default(): static
    {
        return new self();
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
