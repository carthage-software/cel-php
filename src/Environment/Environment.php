<?php

declare(strict_types=1);

namespace Cel\Environment;

use Cel\Exception\IncompatibleValueTypeException;
use Cel\Value\Resolver\DefaultValueResolver;
use Cel\Value\Resolver\ValueResolverInterface;
use Cel\Value\Value;
use Override;

use function array_key_exists;
use function array_map;
use function array_reverse;
use function array_slice;
use function assert;
use function count;

/**
 * A mutable environment for the CEL runtime.
 */
final class Environment implements EnvironmentInterface
{
    /**
     * @var non-empty-list<ValueResolverInterface>
     */
    private array $valueResolvers;

    /**
     * @param array<string, Value> $variables
     * @param list<ValueResolverInterface> $valueResolvers
     */
    public function __construct(
        private array $variables = [],
        array $valueResolvers = [],
    ) {
        // Always include the default resolver as fallback
        $this->valueResolvers = [...$valueResolvers, new DefaultValueResolver()];
    }

    /**
     * Creates a default empty environment.
     *
     * @return static
     */
    #[Override]
    public static function default(): static
    {
        return new self();
    }

    /**
     * Create a new environment from an associative array of variables.
     *
     * The values will be converted to `Value` instances.
     *
     * @param array<string, mixed> $variables Associative array of variable names to values.
     *
     * @return self New environment instance.
     *
     * @throws IncompatibleValueTypeException If the value type is not supported.
     */
    public static function fromArray(array $variables): self
    {
        $variables = array_map(Value::from(...), $variables);

        return new self($variables);
    }

    #[Override]
    public function addVariable(string $name, Value $value): void
    {
        $this->variables[$name] = $value;
    }

    #[Override]
    public function addRaw(string $name, mixed $value): void
    {
        // Try resolvers in reverse order (most recently registered first)
        // Exclude the last one (default resolver) from the reverse iteration
        $count = count($this->valueResolvers);
        $length = $count > 0 ? $count - 1 : 0;
        $customResolvers = array_slice($this->valueResolvers, 0, $length);

        foreach (array_reverse($customResolvers) as $resolver) {
            if (!$resolver->canResolve($value)) {
                continue;
            }

            $this->addVariable($name, $resolver->resolve($value));
            return;
        }

        // Fall back to the default resolver (always the last one)
        $lastIndex = $count - 1;
        assert(array_key_exists($lastIndex, $this->valueResolvers));
        $defaultResolver = $this->valueResolvers[$lastIndex];
        $this->addVariable($name, $defaultResolver->resolve($value));
    }

    #[Override]
    public function registerValueResolver(ValueResolverInterface $resolver): void
    {
        // Insert before the default resolver (which is always last)
        $count = count($this->valueResolvers);
        $lastIndex = $count - 1;
        $length = $count > 0 ? $lastIndex : 0;
        assert(array_key_exists($lastIndex, $this->valueResolvers));
        $this->valueResolvers = [
            ...array_slice($this->valueResolvers, 0, $length),
            $resolver,
            $this->valueResolvers[$lastIndex],
        ];
    }

    #[Override]
    public function hasVariable(string $name): bool
    {
        return array_key_exists($name, $this->variables);
    }

    #[Override]
    public function getVariable(string $name): null|Value
    {
        return $this->variables[$name] ?? null;
    }

    #[Override]
    public function fork(): EnvironmentInterface
    {
        // Exclude the default resolver since constructor will add it
        $count = count($this->valueResolvers);
        $length = $count > 0 ? $count - 1 : 0;
        $customResolvers = array_slice($this->valueResolvers, 0, $length);
        return new self($this->variables, $customResolvers);
    }
}
