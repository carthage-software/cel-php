<?php

declare(strict_types=1);

namespace Cel\Environment;

use Cel\Value\Resolver\ValueResolverInterface;
use Cel\Value\Value;
use Psl\Default\DefaultInterface;

/**
 * Defines the contract for the CEL runtime environment.
 */
interface EnvironmentInterface extends DefaultInterface
{
    public function hasVariable(string $name): bool;

    public function addVariable(string $name, Value $value): void;

    /**
     * Adds a raw PHP value to the environment.
     *
     * The value will be converted to a CEL Value using registered value resolvers.
     *
     * @param string $name The variable name
     * @param mixed $value The raw PHP value
     *
     * @throws IncompatibleValueTypeException If no resolver can handle the value
     */
    public function addRaw(string $name, mixed $value): void;

    public function getVariable(string $name): null|Value;

    /**
     * Registers a value resolver with this environment.
     *
     * Value resolvers are tried in reverse order of registration
     * (most recently registered first), with the default resolver as fallback.
     *
     * @param ValueResolverInterface $resolver The value resolver to register
     */
    public function registerValueResolver(ValueResolverInterface $resolver): void;

    public function fork(): EnvironmentInterface;
}
