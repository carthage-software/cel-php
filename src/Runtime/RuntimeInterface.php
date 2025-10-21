<?php

declare(strict_types=1);

namespace Cel\Runtime;

use Cel\Exception\ConflictingFunctionSignatureException;
use Cel\Exception\EvaluationException;
use Cel\Extension\ExtensionInterface;
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
     * @throws EvaluationException on runtime errors.
     */
    public function run(Expression $expression, array $context = []): RuntimeReceipt;
}
