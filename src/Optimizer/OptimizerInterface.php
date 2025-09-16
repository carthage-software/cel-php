<?php

declare(strict_types=1);

namespace Cel\Optimizer;

use Cel\Syntax\Expression;
use Psl\Default\DefaultInterface;

/**
 * Interface for expression optimizers.
 */
interface OptimizerInterface extends DefaultInterface
{
    /**
     * Adds an optimization pass to the optimizer.
     *
     * @param Optimization\OptimizationInterface $optimization The optimization to add.
     */
    public function addOptimization(Optimization\OptimizationInterface $optimization): void;

    /**
     * Optimizes the given expression by recursively applying all configured optimization passes.
     *
     * @param Expression $expression The expression to optimize.
     *
     * @return Expression The optimized expression.
     */
    public function optimize(Expression $expression): Expression;
}
