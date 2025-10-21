<?php

declare(strict_types=1);

namespace Cel\Tests\Syntax;

use Cel\Span\Span;
use Cel\Syntax\ConditionalExpression;
use Cel\Syntax\ExpressionKind;
use Cel\Syntax\IdentifierNode;
use Cel\Syntax\Member\IdentifierExpression;
use PHPUnit\Framework\TestCase;

final class ConditionalExpressionTest extends TestCase
{
    public function testConstructorAndGetters(): void
    {
        $cond = new IdentifierExpression(new IdentifierNode('a', new Span(0, 1)));
        $q = new Span(2, 3);
        $then = new IdentifierExpression(new IdentifierNode('b', new Span(4, 5)));
        $c = new Span(6, 7);
        $else = new IdentifierExpression(new IdentifierNode('c', new Span(8, 9)));

        $expr = new ConditionalExpression($cond, $q, $then, $c, $else);

        static::assertSame($cond, $expr->condition);
        static::assertSame($q, $expr->question);
        static::assertSame($then, $expr->then);
        static::assertSame($c, $expr->colon);
        static::assertSame($else, $expr->else);
        static::assertSame(ExpressionKind::Conditional, $expr->getKind());
        static::assertSame([$cond, $then, $else], $expr->getChildren());
        $span = $expr->getSpan();
        static::assertSame(0, $span->start);
        static::assertSame(9, $span->end);
    }
}
