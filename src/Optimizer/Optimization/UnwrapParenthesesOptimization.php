<?php

declare(strict_types=1);

namespace Cel\Optimizer\Optimization;

use Cel\Syntax\Expression;
use Cel\Syntax\ParenthesizedExpression;
use Override;

/**
 * Removes parentheses from an expression.
 *
 * Optimization: `(expr)` -> `expr`
 */
final readonly class UnwrapParenthesesOptimization implements OptimizationInterface
{
    /**
     * @inheritDoc
     */
    #[Override]
    public function apply(Expression $expression): null|Expression
    {
        if ($expression instanceof ParenthesizedExpression) {
            return $expression->expression;
        }

        return null;
    }
}
