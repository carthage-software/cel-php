<?php

declare(strict_types=1);

namespace Cel\Tests\Syntax\Unary;

use Cel\Span\Span;
use Cel\Syntax\ExpressionKind;
use Cel\Syntax\IdentifierNode;
use Cel\Syntax\Member\IdentifierExpression;
use Cel\Syntax\Unary\UnaryExpression;
use Cel\Syntax\Unary\UnaryOperator;
use Cel\Syntax\Unary\UnaryOperatorKind;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\Attributes\UsesClass;
use PHPUnit\Framework\TestCase;

#[CoversClass(UnaryExpression::class)]
#[UsesClass(UnaryOperator::class)]
#[UsesClass(IdentifierNode::class)]
#[UsesClass(Span::class)]
#[UsesClass(IdentifierExpression::class)]
final class UnaryExpressionTest extends TestCase
{
    public function testConstructorAndGetters(): void
    {
        $op = new UnaryOperator(UnaryOperatorKind::Not, new Span(0, 1));
        $operand = new IdentifierExpression(new IdentifierNode('a', new Span(1, 2)));

        $expr = new UnaryExpression($op, $operand);

        static::assertSame($op, $expr->operator);
        static::assertSame($operand, $expr->operand);
        static::assertSame(ExpressionKind::Unary, $expr->getKind());
        static::assertSame([$op, $operand], $expr->getChildren());
        $span = $expr->getSpan();
        static::assertSame(0, $span->start);
        static::assertSame(2, $span->end);
    }
}
