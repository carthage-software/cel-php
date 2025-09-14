<?php

declare(strict_types=1);

namespace Cel\Syntax\Signer;

use Cel\Syntax\Expression;

/**
 * Defines a contract for services that create a stable, string-based signature
 * for a given CEL expression AST.
 *
 * This signature is primarily intended for use as a cache key for memoizing
 * the results of expression evaluations.
 *
 * A specific `SignerInterface` implementation guarantees that a given expression
 * object will always produce the same signature. However, two logically
 * equivalent but structurally different expressions (e.g., `foo(1 + 2)` vs.
 * `foo(3)`) may or may not produce the same signature. This behavior depends
 * entirely on the implementation and whether it performs optimizations like
 * constant folding before signing.
 */
interface SignerInterface
{
    /**
     * Creates a stable signature for the given expression.
     *
     * @param Expression $expression The expression to be signed.
     *
     * @return string The signed representation of the expression, suitable for use as a cache key.
     */
    public function sign(Expression $expression): string;
}
