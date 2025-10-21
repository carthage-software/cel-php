<?php

declare(strict_types=1);

namespace Cel\Runtime;

use Cel\Environment\Environment;
use Cel\Environment\EnvironmentInterface;
use Cel\Exception\ConflictingFunctionSignatureException;
use Cel\Exception\EvaluationException;
use Cel\Exception\MisconfigurationException;
use Cel\Extension\ExtensionInterface;
use Cel\Interpreter\Interpreter;
use Cel\Message\MessageInterface;
use Cel\Syntax\Expression;
use Override;
use Psl\Iter;

final class Runtime implements RuntimeInterface
{
    /**
     * @var array<class-string<MessageInterface>, list<string>>
     */
    private array $extensionProvidedMessageTypes = [];

    /**
     * The internal environment that holds value resolvers from registered extensions.
     */
    private EnvironmentInterface $environment;

    /**
     * @throws MisconfigurationException if configuration validation fails.
     * @throws ConflictingFunctionSignatureException if a function with the same name and signature already exists.
     */
    public function __construct(
        private readonly Configuration $configuration = new Configuration(),
        private readonly OperationRegistry $registry = new OperationRegistry(),
    ) {
        // Create internal environment
        $this->environment = Environment::default();

        // Register all extensions from configuration
        foreach ($this->configuration->getExtensions() as $extension) {
            $this->register($extension);
        }
    }

    /**
     * @inheritDoc
     */
    #[Override]
    public static function default(): static
    {
        return new self();
    }

    /**
     * @inheritDoc
     */
    #[Override]
    public function register(ExtensionInterface $extension): void
    {
        $this->registry->register($extension);

        // Register value resolvers from the extension into the internal environment
        foreach ($extension->getValueResolvers() as $resolver) {
            $this->environment->registerValueResolver($resolver);
        }

        // Collect message types provided by the extension
        foreach ($extension->getMessageTypes() as $messageClass => $aliases) {
            if (!isset($this->extensionProvidedMessageTypes[$messageClass])) {
                $this->extensionProvidedMessageTypes[$messageClass] = [];
            }

            $this->extensionProvidedMessageTypes[$messageClass] = [
                ...$this->extensionProvidedMessageTypes[$messageClass],
                ...$aliases,
            ];
        }
    }

    /**
     * Evaluates the given expression with the provided context.
     *
     * The runtime maintains an internal environment with value resolvers from registered extensions.
     * For each run, it forks this environment and populates it with the provided context variables.
     *
     * @param Expression $expression The expression to evaluate.
     * @param array<string, mixed> $context Associative array of variable names to values for this execution.
     *
     * @return RuntimeReceipt The result of the evaluation, including the value and any relevant metadata.
     *
     * @throws MisconfigurationException if configuration validation fails.
     * @throws EvaluationException on runtime errors.
     */
    #[Override]
    public function run(Expression $expression, array $context = []): RuntimeReceipt
    {
        // Fork the internal environment to get a fresh environment with all registered value resolvers
        $environment = $this->environment->fork();

        // Add context variables to the forked environment
        foreach ($context as $name => $value) { // @mago-expect analysis:mixed-assignment
            $environment->addRaw($name, $value);
        }

        // Merge user-provided message types with extension-provided message types
        $mergedConfiguration = $this->createMergedConfiguration();

        $interpreter = new Interpreter($mergedConfiguration, $this->registry, $environment);
        $interpreter->reset(); // Ensure the interpreter is in a clean state before running.

        $result = $interpreter->run($expression);
        $idempotent = $interpreter->wasIdempotent();

        $interpreter->reset(); // Reset the interpreter state after running, in case of reuse.

        return new RuntimeReceipt($result, $idempotent);
    }

    /**
     * Creates a merged configuration that combines user-provided message types
     * with extension-provided message types.
     *
     * @throws MisconfigurationException if configuration validation fails.
     */
    private function createMergedConfiguration(): Configuration
    {
        // Start with user-provided message classes
        $mergedAllowedMessageClasses = $this->configuration->allowedMessageClasses;
        $mergedMessageClassAliases = $this->configuration->messageClassAliases;

        // Add extension-provided message types
        foreach ($this->extensionProvidedMessageTypes as $messageClass => $aliases) {
            // Add the class to allowed list if not already present
            if (!Iter\contains($mergedAllowedMessageClasses, $messageClass)) {
                $mergedAllowedMessageClasses[] = $messageClass;
            }

            // Add aliases for this message class
            foreach ($aliases as $alias) {
                $mergedMessageClassAliases[$alias] = $messageClass;
            }
        }

        // Create a new configuration with merged message types
        $mergedConfig = new Configuration(
            enableMacros: $this->configuration->enableMacros,
            allowedMessageClasses: $mergedAllowedMessageClasses,
            messageClassAliases: $mergedMessageClassAliases,
            enforceMessageClassAliases: $this->configuration->enforceMessageClassAliases,
            enableStandardExtensions: false, // Don't re-register extensions
        );

        // Copy extensions from original configuration
        foreach ($this->configuration->getExtensions() as $extension) {
            $mergedConfig->addExtension($extension);
        }

        return $mergedConfig;
    }
}
