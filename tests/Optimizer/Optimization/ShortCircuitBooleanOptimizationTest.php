<?php

declare(strict_types=1);

namespace Cel\Tests\Optimizer\Optimization;

use Cel\Optimizer\Optimization\OptimizationInterface;
use Cel\Optimizer\Optimization\ShortCircuitBooleanOptimization;
use Cel\Span\Span;
use Cel\Syntax\Binary\BinaryExpression;
use Cel\Syntax\Binary\BinaryOperator;
use Cel\Syntax\Binary\BinaryOperatorKind;
use Cel\Syntax\IdentifierNode;
use Cel\Syntax\Literal\BoolLiteralExpression;
use Cel\Syntax\Member\IdentifierExpression;
use Override;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\Attributes\UsesClass;

#[CoversClass(ShortCircuitBooleanOptimization::class)]
#[UsesClass(BinaryExpression::class)]
#[UsesClass(BoolLiteralExpression::class)]
#[UsesClass(IdentifierExpression::class)]
#[UsesClass(BinaryOperator::class)]
#[UsesClass(BinaryOperatorKind::class)]
#[UsesClass(IdentifierNode::class)]
#[UsesClass(Span::class)]
final class ShortCircuitBooleanOptimizationTest extends OptimizationTestCase
{
    #[Override]
    public function createOptimization(): OptimizationInterface
    {
        return new ShortCircuitBooleanOptimization();
    }

    #[Override]
    public static function provideOptimizationCases(): iterable
    {
        $ident = new IdentifierExpression(new IdentifierNode('a', Span::zero()));
        $true = new BoolLiteralExpression(true, 'true', Span::zero());
        $false = new BoolLiteralExpression(false, 'false', Span::zero());
        $and = new BinaryOperator(BinaryOperatorKind::And, Span::zero());
        $or = new BinaryOperator(BinaryOperatorKind::Or, Span::zero());
        $plus = new BinaryOperator(BinaryOperatorKind::Plus, Span::zero());

        // AND cases
        yield 'expr && true -> expr' => [
            new BinaryExpression($ident, $and, $true),
            $ident,
        ];
        yield 'true && expr -> expr' => [
            new BinaryExpression($true, $and, $ident),
            $ident,
        ];
        yield 'expr && false -> false' => [
            new BinaryExpression($ident, $and, $false),
            $false,
        ];
        yield 'false && expr -> false' => [
            new BinaryExpression($false, $and, $ident),
            $false,
        ];

        // OR cases
        yield 'expr || true -> true' => [
            new BinaryExpression($ident, $or, $true),
            $true,
        ];
        yield 'true || expr -> true' => [
            new BinaryExpression($true, $or, $ident),
            $true,
        ];
        yield 'expr || false -> expr' => [
            new BinaryExpression($ident, $or, $false),
            $ident,
        ];
        yield 'false || expr -> expr' => [
            new BinaryExpression($false, $or, $ident),
            $ident,
        ];

        // Non-applicable cases
        yield 'Does not affect non-boolean binary op' => [
            new BinaryExpression($ident, $plus, $ident),
            null,
        ];
        yield 'Does not affect non-binary expression' => [
            $ident,
            null,
        ];
    }
}
