<?php

declare(strict_types=1);

namespace Cel\Tests\Optimizer\Optimization;

use Cel\Optimizer\Optimization\OptimizationInterface;
use Cel\Optimizer\Optimization\UnwrapParenthesesOptimization;
use Cel\Span\Span;
use Cel\Syntax\IdentifierNode;
use Cel\Syntax\Member\IdentifierExpression;
use Cel\Syntax\ParenthesizedExpression;
use Override;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\Attributes\UsesClass;

#[CoversClass(UnwrapParenthesesOptimization::class)]
#[UsesClass(ParenthesizedExpression::class)]
#[UsesClass(IdentifierExpression::class)]
#[UsesClass(IdentifierNode::class)]
#[UsesClass(Span::class)]
final class UnwrapParenthesesOptimizationTest extends OptimizationTestCase
{
    #[Override]
    public function createOptimization(): OptimizationInterface
    {
        return new UnwrapParenthesesOptimization();
    }

    /**
     * @inheritDoc
     */
    #[Override]
    public static function provideOptimizationCases(): iterable
    {
        $ident = new IdentifierExpression(new IdentifierNode('a', Span::zero()));

        yield 'Unwraps parentheses' => [new ParenthesizedExpression(Span::zero(), $ident, Span::zero()), $ident];
        yield 'Does not affect other nodes' => [$ident, null];
    }
}
