<?php

declare(strict_types=1);

namespace Cel\Optimizer\Optimization;

use Cel\Syntax\Expression;

/**
 * Defines the contract for a single, targeted optimization pass.
 */
interface OptimizationInterface
{
    /**
     * Attempts to apply the optimization to the given expression node.
     *
     * If the optimization can be applied, this method should return the new,
     * optimized Expression node. If the optimization does not apply to this
     * node, it must return null.
     *
     * @param Expression $expression The expression node to optimize.
     *
     * @return null|Expression The optimized expression, or null if no change was made.
     */
    public function apply(Expression $expression): null|Expression;
}
