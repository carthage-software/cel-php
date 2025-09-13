<?php

declare(strict_types=1);

namespace Cel\Runtime;

use Cel\Runtime\Environment\EnvironmentInterface;
use Cel\Runtime\Exception\ConflictingFunctionSignatureException;
use Cel\Runtime\Extension\ExtensionInterface;
use Cel\Runtime\Value\Value;
use Cel\Syntax\Expression;

interface RuntimeInterface
{
    /**
     * Registers an extension with the runtime.
     *
     * @throws ConflictingFunctionSignatureException If the extension introduces conflicting function signatures.
     */
    public function register(ExtensionInterface $extension): void;

    /**
     * Evaluates the given expression within the provided environment.
     *
     * @param Expression           $expression  The expression to evaluate.
     * @param EnvironmentInterface $environment The environment in which to evaluate the expression.
     *
     * @return Value The result of the evaluation.
     *
     * @throws Exception\RuntimeException on runtime errors.
     */
    public function run(Expression $expression, EnvironmentInterface $environment): Value;
}
