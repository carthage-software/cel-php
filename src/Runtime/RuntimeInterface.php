<?php

declare(strict_types=1);

namespace Cel\Runtime;

use Cel\Runtime\Environment\EnvironmentInterface;
use Cel\Runtime\Exception\ConflictingFunctionSignatureException;
use Cel\Runtime\Extension\ExtensionInterface;
use Cel\Syntax\Expression;
use Psl\Default\DefaultInterface;

interface RuntimeInterface extends DefaultInterface
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
     * @return RuntimeReceipt The result of the evaluation, including the value and any relevant metadata.
     *
     * @throws Exception\EvaluationException on runtime errors.
     */
    public function run(Expression $expression, EnvironmentInterface $environment): RuntimeReceipt;
}
