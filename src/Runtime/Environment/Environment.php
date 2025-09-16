<?php

declare(strict_types=1);

namespace Cel\Runtime\Environment;

use Cel\Runtime\Exception\IncompatibleValueTypeException;
use Cel\Runtime\Value\Value;
use Override;
use Psl\Dict;
use Psl\Iter;

/**
 * A mutable environment for the CEL runtime.
 */
final class Environment implements EnvironmentInterface
{
    /**
     * @param array<string, Value> $variables
     */
    public function __construct(
        private array $variables = [],
    ) {}

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
        return new self($this->variables);
    }
}
