<?php

declare(strict_types=1);

namespace Cel\Optimizer\Optimization;

use Cel\Runtime\Runtime;
use Cel\Runtime\RuntimeInterface;
use Cel\Span\Span;
use Cel\Syntax\Binary\BinaryExpression;
use Cel\Syntax\Expression;
use Cel\Syntax\Literal\BoolLiteralExpression;
use Cel\Syntax\Literal\BytesLiteralExpression;
use Cel\Syntax\Literal\FloatLiteralExpression;
use Cel\Syntax\Literal\IntegerLiteralExpression;
use Cel\Syntax\Literal\NullLiteralExpression;
use Cel\Syntax\Literal\StringLiteralExpression;
use Cel\Syntax\Literal\UnsignedIntegerLiteralExpression;
use Cel\Syntax\Unary\UnaryExpression;
use Cel\Value\BooleanValue;
use Cel\Value\BytesValue;
use Cel\Value\FloatValue;
use Cel\Value\IntegerValue;
use Cel\Value\NullValue;
use Cel\Value\StringValue;
use Cel\Value\UnsignedIntegerValue;
use Override;
use Throwable;

use function is_finite;

/**
 * Evaluates constant expressions at compile time.
 *
 * Optimizations:
 *
 * - `1 + 2` -> `3`
 * - `"hello" + " world"` -> `"hello world"`
 * - `true && false` -> `false`
 * - `!true` -> `false`
 * - `-5` -> `-5` (evaluated)
 */
final readonly class ConstantFoldingOptimization implements OptimizationInterface
{
    /**
     * @param RuntimeInterface $runtime The runtime used to evaluate constant expressions. Using the
     *                                  same runtime that executes the expression ensures folding
     *                                  honours registered extensions and custom operator overloads.
     */
    public function __construct(
        private RuntimeInterface $runtime = new Runtime(),
    ) {}

    #[Override]
    public function apply(Expression $expression): null|Expression
    {
        // Check if expression is a binary operation with both sides being literals
        if (
            $expression instanceof BinaryExpression
            && $this->isLiteral($expression->left)
            && $this->isLiteral($expression->right)
        ) {
            return $this->fold($expression);
        }

        // Check if expression is a unary operation with a literal operand
        if ($expression instanceof UnaryExpression && $this->isLiteral($expression->operand)) {
            return $this->fold($expression);
        }

        return null;
    }

    private function isLiteral(Expression $expression): bool
    {
        return (
            $expression instanceof IntegerLiteralExpression
            || $expression instanceof UnsignedIntegerLiteralExpression
            || $expression instanceof FloatLiteralExpression
            || $expression instanceof StringLiteralExpression
            || $expression instanceof BoolLiteralExpression
            || $expression instanceof NullLiteralExpression
            || $expression instanceof BytesLiteralExpression
        );
    }

    private function fold(Expression $expression): null|Expression
    {
        try {
            $receipt = $this->runtime->run($expression, []);
        } catch (Throwable) {
            // If evaluation fails (e.g. no such overload, division by zero), leave it for runtime.
            return null;
        }

        // Only fold results that are stable across evaluations; a non-idempotent
        // sub-expression must be evaluated at runtime, not baked into a literal.
        if (!$receipt->idempotent) {
            return null;
        }

        return $this->valueToLiteral($receipt->result, $expression->getSpan());
    }

    private function valueToLiteral(mixed $value, Span $span): null|Expression
    {
        if ($value instanceof IntegerValue) {
            $nativeValue = $value->getRawValue();
            return new IntegerLiteralExpression($nativeValue, (string) $nativeValue, $span);
        }

        if ($value instanceof UnsignedIntegerValue) {
            $nativeValue = $value->getRawValue();
            return new UnsignedIntegerLiteralExpression($nativeValue, $nativeValue . 'u', $span);
        }

        if ($value instanceof FloatValue) {
            if (!is_finite($value->value)) {
                return null;
            }

            return new FloatLiteralExpression($value->value, (string) $value->value, $span);
        }

        if ($value instanceof StringValue) {
            return new StringLiteralExpression($value->value, '"' . $value->value . '"', $span);
        }

        if ($value instanceof BooleanValue) {
            $literal = $value->value ? 'true' : 'false';
            return new BoolLiteralExpression($value->value, $literal, $span);
        }

        if ($value instanceof NullValue) {
            return new NullLiteralExpression('null', $span);
        }

        if ($value instanceof BytesValue) {
            return new BytesLiteralExpression($value->value, 'b"' . $value->value . '"', $span);
        }

        return null;
    }
}
