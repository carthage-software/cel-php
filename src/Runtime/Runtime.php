<?php

declare(strict_types=1);

namespace Cel\Runtime;

use Cel\Environment\Environment;
use Cel\Environment\EnvironmentInterface;
use Cel\Exception\ConflictingFunctionSignatureException;
use Cel\Exception\EvaluationException;
use Cel\Exception\InternalException;
use Cel\Exception\MisconfigurationException;
use Cel\Extension\ExtensionInterface;
use Cel\Interpreter\Interpreter;
use Cel\Message\MessageInterface;
use Cel\Syntax\Expression;
use Cel\VirtualMachine\Compiler;
use Cel\VirtualMachine\Program;
use Cel\VirtualMachine\VirtualMachine;
use Override;
use Psl\Iter;
use Psr\SimpleCache\CacheInterface;
use Psr\SimpleCache\InvalidArgumentException;

use function hash;
use function serialize;

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
     * Cached merged configuration (invalidated on register() calls).
     */
    private ?Configuration $mergedConfiguration = null;

    /**
     * Cached compiler instance for VM backend (invalidated on register() calls).
     */
    private ?Compiler $vmCompiler = null;

    /**
     * Cached VM instance (invalidated on register() calls).
     */
    private ?VirtualMachine $vm = null;

    private const string PROGRAM_CACHE_KEY_PREFIX = 'cel_prog_';

    /**
     * @throws MisconfigurationException if configuration validation fails.
     * @throws ConflictingFunctionSignatureException if a function with the same name and signature already exists.
     */
    public function __construct(
        private readonly Configuration $configuration = new Configuration(),
        private readonly OperationRegistry $registry = new OperationRegistry(),
        private readonly ?CacheInterface $cache = null,
        private readonly ?int $cacheTtl = 3600,
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

        // Invalidate cached objects that depend on registry/configuration state
        $this->mergedConfiguration = null;
        $this->vmCompiler = null;
        $this->vm = null;

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
        $environment = $this->environment->fork();

        foreach ($context as $name => $value) { // @mago-expect analysis:mixed-assignment
            $environment->addRaw($name, $value);
        }

        $this->mergedConfiguration ??= $this->createMergedConfiguration();
        $mergedConfiguration = $this->mergedConfiguration;

        if ($mergedConfiguration->executionBackend === ExecutionBackend::VirtualMachine) {
            $program = $this->compileOrLoadProgram($expression, $mergedConfiguration);
            $this->vm ??= new VirtualMachine($mergedConfiguration, $this->registry);
            $result = $this->vm->execute($program, $environment);
            $idempotent = $this->vm->wasIdempotent();
        } else {
            $interpreter = new Interpreter($mergedConfiguration, $this->registry, $environment);
            $interpreter->reset();
            $result = $interpreter->run($expression);
            $idempotent = $interpreter->wasIdempotent();
            $interpreter->reset();
        }

        return new RuntimeReceipt($result, $idempotent);
    }

    /**
     * Compiles the expression to bytecode, using PSR-16 cache when available.
     *
     * The Compiler also has a 1-entry identity cache that skips recompilation
     * when the same Expression object is passed consecutively. The PSR-16 cache
     * extends this across requests (filesystem, Redis, etc.).
     */
    private function compileOrLoadProgram(Expression $expression, Configuration $mergedConfiguration): Program
    {
        $this->vmCompiler ??= new Compiler($mergedConfiguration);

        if (null === $this->cache) {
            return $this->vmCompiler->compile($expression);
        }

        $cacheKey = self::PROGRAM_CACHE_KEY_PREFIX . hash('xxh128', serialize($expression));

        try {
            /** @var null|Program */
            $cached = $this->cache->get($cacheKey);
        } catch (InvalidArgumentException $e) {
            throw InternalException::forMessage('Cache operation failed: ' . $e->getMessage());
        }

        if ($cached instanceof Program) {
            return $cached;
        }

        $program = $this->vmCompiler->compile($expression);

        try {
            $this->cache->set($cacheKey, $program, $this->cacheTtl);
        } catch (InvalidArgumentException $e) {
            throw InternalException::forMessage('Cache operation failed: ' . $e->getMessage());
        }

        return $program;
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
            executionBackend: $this->configuration->executionBackend,
        );

        // Copy extensions from original configuration
        foreach ($this->configuration->getExtensions() as $extension) {
            $mergedConfig->addExtension($extension);
        }

        return $mergedConfig;
    }
}
