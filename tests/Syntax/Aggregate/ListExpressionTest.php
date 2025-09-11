<?php

declare(strict_types=1);

namespace Cel\Tests\Syntax\Aggregate;

use Cel\Span\Span;
use Cel\Syntax\Aggregate\ListExpression;
use Cel\Syntax\Expression;
use Cel\Syntax\ExpressionKind;
use Cel\Syntax\IdentifierNode;
use Cel\Syntax\Member\IdentifierExpression;
use Cel\Syntax\PunctuatedSequence;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\Attributes\UsesClass;
use PHPUnit\Framework\TestCase;

#[CoversClass(ListExpression::class)]
#[UsesClass(PunctuatedSequence::class)]
#[UsesClass(IdentifierNode::class)]
#[UsesClass(Span::class)]
#[UsesClass(IdentifierExpression::class)]
final class ListExpressionTest extends TestCase
{
    public function testConstructorAndGetters(): void
    {
        $open = new Span(0, 1);
        $close = new Span(1, 2);
        /** @var PunctuatedSequence<Expression> */
        $elements = new PunctuatedSequence([], []);

        $expr = new ListExpression($open, $elements, $close);

        static::assertSame($open, $expr->openingBracket);
        static::assertSame($elements, $expr->elements);
        static::assertSame($close, $expr->closingBracket);
        static::assertSame(ExpressionKind::List, $expr->getKind());
        static::assertEmpty($expr->getChildren());
        $span = $expr->getSpan();
        static::assertSame(0, $span->start);
        static::assertSame(2, $span->end);
    }

    public function testGetChildrenWithElements(): void
    {
        $open = new Span(0, 1);
        $el1 = new IdentifierExpression(new IdentifierNode('a', new Span(1, 2)));
        $el2 = new IdentifierExpression(new IdentifierNode('b', new Span(3, 4)));
        $elements = new PunctuatedSequence([$el1, $el2], [new Span(2, 3)]);
        $close = new Span(4, 5);

        $expr = new ListExpression($open, $elements, $close);

        static::assertSame([$el1, $el2], $expr->getChildren());
    }
}
