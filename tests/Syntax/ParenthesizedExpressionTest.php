<?php

declare(strict_types=1);

namespace Cel\Tests\Syntax;

use Cel\Span\Span;
use Cel\Syntax\ExpressionKind;
use Cel\Syntax\IdentifierNode;
use Cel\Syntax\Member\IdentifierExpression;
use Cel\Syntax\ParenthesizedExpression;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\Attributes\UsesClass;
use PHPUnit\Framework\TestCase;

#[CoversClass(ParenthesizedExpression::class)]
#[UsesClass(IdentifierNode::class)]
#[UsesClass(Span::class)]
#[UsesClass(IdentifierExpression::class)]
final class ParenthesizedExpressionTest extends TestCase
{
    public function testConstructorAndGetters(): void
    {
        $left = new Span(0, 1);
        $inner = new IdentifierExpression(new IdentifierNode('a', new Span(1, 2)));
        $right = new Span(2, 3);

        $expr = new ParenthesizedExpression($left, $inner, $right);

        static::assertSame($left, $expr->leftParenthesis);
        static::assertSame($inner, $expr->expression);
        static::assertSame($right, $expr->rightParenthesis);
        static::assertSame(ExpressionKind::Parenthesized, $expr->getKind());
        static::assertSame([$inner], $expr->getChildren());
        $span = $expr->getSpan();
        static::assertSame(0, $span->start);
        static::assertSame(3, $span->end);
    }
}
