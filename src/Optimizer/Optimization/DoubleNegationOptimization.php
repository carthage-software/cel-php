<?php

declare(strict_types=1);

namespace Cel\Optimizer\Optimization;

use Cel\Syntax\Expression;
use Cel\Syntax\Unary\UnaryExpression;
use Override;

/**
 * Simplifies double negation expressions.
 *
 * Optimizations:
 *
 * - `!!expr` -> `expr`
 * - `--expr` -> `expr`
 */
final readonly class DoubleNegationOptimization implements OptimizationInterface
{
    #[Override]
    public function apply(Expression $expression): null|Expression
    {
        if (!$expression instanceof UnaryExpression) {
            return null;
        }

        if (
            $expression->operand instanceof UnaryExpression
            && $expression->operator->kind === $expression->operand->operator->kind
        ) {
            return $expression->operand->operand;
        }

        return null;
    }
}
