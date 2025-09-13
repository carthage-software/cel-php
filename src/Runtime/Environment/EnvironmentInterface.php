<?php

declare(strict_types=1);

namespace Cel\Runtime\Environment;

use Cel\Runtime\Value\Value;

/**
 * Defines the contract for the CEL runtime environment.
 */
interface EnvironmentInterface
{
    public function hasVariable(string $name): bool;

    public function addVariable(string $name, Value $value): void;

    public function getVariable(string $name): null|Value;
}
