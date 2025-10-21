<?php

declare(strict_types=1);

namespace Cel\Tests\Syntax\Binary;

use Cel\Span\Span;
use Cel\Syntax\Binary\BinaryExpression;
use Cel\Syntax\Binary\BinaryOperator;
use Cel\Syntax\Binary\BinaryOperatorKind;
use Cel\Syntax\ExpressionKind;
use Cel\Syntax\IdentifierNode;
use Cel\Syntax\Member\IdentifierExpression;
use PHPUnit\Framework\TestCase;

final class BinaryExpressionTest extends TestCase
{
    public function testConstructorAndGetters(): void
    {
        $left = new IdentifierExpression(new IdentifierNode('a', new Span(0, 1)));
        $op = new BinaryOperator(BinaryOperatorKind::Plus, new Span(2, 3));
        $right = new IdentifierExpression(new IdentifierNode('b', new Span(4, 5)));

        $expr = new BinaryExpression($left, $op, $right);

        static::assertSame($left, $expr->left);
        static::assertSame($op, $expr->operator);
        static::assertSame($right, $expr->right);
        static::assertSame(ExpressionKind::Binary, $expr->getKind());
        static::assertSame([$left, $op, $right], $expr->getChildren());
        $span = $expr->getSpan();
        static::assertSame(0, $span->start);
        static::assertSame(5, $span->end);
    }
}
