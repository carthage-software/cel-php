<?php

declare(strict_types=1);

namespace Cel\Environment;

use Cel\Exception\IncompatibleValueTypeException;
use Cel\Value\Resolver\DefaultValueResolver;
use Cel\Value\Resolver\ValueResolverInterface;
use Cel\Value\Value;
use Override;
use Psl\Dict;
use Psl\Iter;
use Psl\Vec;

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
        $variables = Dict\map($variables, Value::from(...));

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
        $count = Iter\count($this->valueResolvers);
        $length = $count > 0 ? $count - 1 : 0;
        $customResolvers = Vec\slice($this->valueResolvers, 0, $length);

        foreach (Vec\reverse($customResolvers) as $resolver) {
            if ($resolver->canResolve($value)) {
                $this->addVariable($name, $resolver->resolve($value));
                return;
            }
        }

        // Fall back to the default resolver (always the last one)
        $defaultResolver = $this->valueResolvers[$count - 1];
        $this->addVariable($name, $defaultResolver->resolve($value));
    }

    #[Override]
    public function registerValueResolver(ValueResolverInterface $resolver): void
    {
        // Insert before the default resolver (which is always last)
        $count = Iter\count($this->valueResolvers);
        $length = $count > 0 ? $count - 1 : 0;
        $this->valueResolvers = [
            ...Vec\slice($this->valueResolvers, 0, $length),
            $resolver,
            $this->valueResolvers[$count - 1],
        ];
    }

    #[Override]
    public function hasVariable(string $name): bool
    {
        return Iter\contains_key($this->variables, $name);
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
        $count = Iter\count($this->valueResolvers);
        $length = $count > 0 ? $count - 1 : 0;
        $customResolvers = Vec\slice($this->valueResolvers, 0, $length);
        return new self($this->variables, $customResolvers);
    }
}
