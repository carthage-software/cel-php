<?php

declare(strict_types=1);

namespace Cel\Optimizer\Optimization;

use Cel\Syntax\Binary\BinaryExpression;
use Cel\Syntax\Binary\BinaryOperatorKind;
use Cel\Syntax\Expression;
use Cel\Syntax\Literal\BoolLiteralExpression;
use Override;

/**
 * Simplifies logical AND (&&) and OR (||) expressions where one side is a constant boolean.
 *
 * Optimizations:
 *
 * - `expr && true`  -> `expr`
 * - `expr && false` -> `false`
 * - `expr || true`  -> `true`
 * - `expr || false` -> `expr`
 */
final readonly class ShortCircuitBooleanOptimization implements OptimizationInterface
{
    #[Override]
    public function apply(Expression $expression): null|Expression
    {
        if (!$expression instanceof BinaryExpression) {
            return null;
        }

        if ($expression->operator->kind === BinaryOperatorKind::And) {
            return $this->optimizeAnd($expression);
        }

        if ($expression->operator->kind === BinaryOperatorKind::Or) {
            return $this->optimizeOr($expression);
        }

        return null;
    }

    private function optimizeAnd(BinaryExpression $expr): null|Expression
    {
        if ($expr->left instanceof BoolLiteralExpression) {
            return $expr->left->value ? $expr->right : $expr->left;
        }

        if ($expr->right instanceof BoolLiteralExpression) {
            return $expr->right->value ? $expr->left : $expr->right;
        }

        return null;
    }

    private function optimizeOr(BinaryExpression $expr): null|Expression
    {
        if ($expr->left instanceof BoolLiteralExpression) {
            return $expr->left->value ? $expr->left : $expr->right;
        }

        if ($expr->right instanceof BoolLiteralExpression) {
            return $expr->right->value ? $expr->right : $expr->left;
        }

        return null;
    }
}
