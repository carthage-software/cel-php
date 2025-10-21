<?php

declare(strict_types=1);

namespace Cel\Optimizer\Optimization;

use Cel\Syntax\ConditionalExpression;
use Cel\Syntax\Expression;
use Cel\Syntax\Literal\BoolLiteralExpression;
use Override;

/**
 * Simplifies conditional (ternary) expressions when the condition is a constant.
 *
 * Optimizations:
 *
 * - `true ? thenExpr : elseExpr` -> `thenExpr`
 * - `false ? thenExpr : elseExpr` -> `elseExpr`
 */
final readonly class ConditionalSimplificationOptimization implements OptimizationInterface
{
    #[Override]
    public function apply(Expression $expression): null|Expression
    {
        if (!$expression instanceof ConditionalExpression) {
            return null;
        }

        if (!$expression->condition instanceof BoolLiteralExpression) {
            return null;
        }

        return $expression->condition->value ? $expression->then : $expression->else;
    }
}
