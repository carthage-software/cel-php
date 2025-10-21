<?php

declare(strict_types=1);

namespace Cel\Optimizer\Optimization;

use Cel\Syntax\Binary\BinaryExpression;
use Cel\Syntax\Binary\BinaryOperatorKind;
use Cel\Syntax\Expression;
use Cel\Syntax\Literal\FloatLiteralExpression;
use Cel\Syntax\Literal\IntegerLiteralExpression;
use Cel\Syntax\Literal\StringLiteralExpression;
use Cel\Syntax\Literal\UnsignedIntegerLiteralExpression;
use Override;

/**
 * Simplifies identity operations where one operand is a neutral element.
 *
 * Optimizations:
 *
 * - `expr + 0` -> `expr`
 * - `0 + expr` -> `expr`
 * - `expr - 0` -> `expr`
 * - `expr * 1` -> `expr`
 * - `1 * expr` -> `expr`
 * - `expr * 0` -> `0`
 * - `0 * expr` -> `0`
 * - `expr / 1` -> `expr`
 * - `expr + ""` -> `expr` (for strings)
 * - `"" + expr` -> `expr` (for strings)
 */
final readonly class IdentityOperationOptimization implements OptimizationInterface
{
    #[Override]
    public function apply(Expression $expression): null|Expression
    {
        if (!$expression instanceof BinaryExpression) {
            return null;
        }

        return match ($expression->operator->kind) {
            BinaryOperatorKind::Plus => $this->optimizeAddition($expression),
            BinaryOperatorKind::Minus => $this->optimizeSubtraction($expression),
            BinaryOperatorKind::Multiply => $this->optimizeMultiplication($expression),
            BinaryOperatorKind::Divide => $this->optimizeDivision($expression),
            default => null,
        };
    }

    private function optimizeAddition(BinaryExpression $expr): null|Expression
    {
        // expr + 0 -> expr
        if ($this->isZero($expr->right)) {
            return $expr->left;
        }

        // 0 + expr -> expr
        if ($this->isZero($expr->left)) {
            return $expr->right;
        }

        // expr + "" -> expr
        if ($this->isEmptyString($expr->right)) {
            return $expr->left;
        }

        // "" + expr -> expr
        if ($this->isEmptyString($expr->left)) {
            return $expr->right;
        }

        return null;
    }

    private function optimizeSubtraction(BinaryExpression $expr): null|Expression
    {
        // expr - 0 -> expr
        if ($this->isZero($expr->right)) {
            return $expr->left;
        }

        return null;
    }

    private function optimizeMultiplication(BinaryExpression $expr): null|Expression
    {
        // expr * 0 -> 0
        if ($this->isZero($expr->right)) {
            return $expr->right;
        }

        // 0 * expr -> 0
        if ($this->isZero($expr->left)) {
            return $expr->left;
        }

        // expr * 1 -> expr
        if ($this->isOne($expr->right)) {
            return $expr->left;
        }

        // 1 * expr -> expr
        if ($this->isOne($expr->left)) {
            return $expr->right;
        }

        return null;
    }

    private function optimizeDivision(BinaryExpression $expr): null|Expression
    {
        // expr / 1 -> expr
        if ($this->isOne($expr->right)) {
            return $expr->left;
        }

        return null;
    }

    private function isZero(Expression $expr): bool
    {
        if ($expr instanceof IntegerLiteralExpression) {
            return $expr->value === 0;
        }

        if ($expr instanceof UnsignedIntegerLiteralExpression) {
            return $expr->value === '0' || $expr->value === 0;
        }

        if ($expr instanceof FloatLiteralExpression) {
            return $expr->value === 0.0;
        }

        return false;
    }

    private function isOne(Expression $expr): bool
    {
        if ($expr instanceof IntegerLiteralExpression) {
            return $expr->value === 1;
        }

        if ($expr instanceof UnsignedIntegerLiteralExpression) {
            return $expr->value === '1' || $expr->value === 1;
        }

        if ($expr instanceof FloatLiteralExpression) {
            return $expr->value === 1.0;
        }

        return false;
    }

    private function isEmptyString(Expression $expr): bool
    {
        return $expr instanceof StringLiteralExpression && $expr->value === '';
    }
}
