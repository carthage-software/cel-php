<?php

declare(strict_types=1);

namespace Cel\Tests\Syntax\Member;

use Cel\Span\Span;
use Cel\Syntax\ExpressionKind;
use Cel\Syntax\IdentifierNode;
use Cel\Syntax\Literal\IntegerLiteralExpression;
use Cel\Syntax\Member\IdentifierExpression;
use Cel\Syntax\Member\IndexExpression;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\Attributes\UsesClass;
use PHPUnit\Framework\TestCase;

#[CoversClass(IndexExpression::class)]
#[UsesClass(IdentifierNode::class)]
#[UsesClass(IntegerLiteralExpression::class)]
#[UsesClass(Span::class)]
#[UsesClass(IdentifierExpression::class)]
final class IndexExpressionTest extends TestCase
{
    public function testConstructorAndGetters(): void
    {
        $operand = new IdentifierExpression(new IdentifierNode('list', new Span(0, 4)));
        $open = new Span(4, 5);
        $index = new IntegerLiteralExpression(1, '1', new Span(5, 6));
        $close = new Span(6, 7);

        $expr = new IndexExpression($operand, $open, $index, $close);

        static::assertSame($operand, $expr->operand);
        static::assertSame($open, $expr->openingBracket);
        static::assertSame($index, $expr->index);
        static::assertSame($close, $expr->closingBracket);
        static::assertSame(ExpressionKind::Index, $expr->getKind());
        static::assertSame([$operand, $index], $expr->getChildren());
        $span = $expr->getSpan();
        static::assertSame(0, $span->start);
        static::assertSame(7, $span->end);
    }
}
