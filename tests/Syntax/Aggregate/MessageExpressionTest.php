<?php

declare(strict_types=1);

namespace Cel\Tests\Syntax\Aggregate;

use Cel\Span\Span;
use Cel\Syntax\Aggregate\FieldInitializerNode;
use Cel\Syntax\Aggregate\MessageExpression;
use Cel\Syntax\Expression;
use Cel\Syntax\ExpressionKind;
use Cel\Syntax\PunctuatedSequence;
use Cel\Syntax\SelectorNode;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\Attributes\UsesClass;
use PHPUnit\Framework\TestCase;

#[CoversClass(MessageExpression::class)]
#[UsesClass(PunctuatedSequence::class)]
#[UsesClass(Span::class)]
#[UsesClass(SelectorNode::class)]
#[UsesClass(FieldInitializerNode::class)]
final class MessageExpressionTest extends TestCase
{
    public function testConstructorAndGetters(): void
    {
        $dot = new Span(0, 1);
        $selector = new SelectorNode('Type', new Span(1, 5));
        /** @var PunctuatedSequence<SelectorNode> */
        $followingSelectors = new PunctuatedSequence([], []);
        $open = new Span(5, 6);
        $close = new Span(6, 7);
        /** @var PunctuatedSequence<FieldInitializerNode> */
        $initializers = new PunctuatedSequence([], []);

        $expr = new MessageExpression($dot, $selector, $followingSelectors, $open, $initializers, $close);

        static::assertSame($dot, $expr->dot);
        static::assertSame($selector, $expr->selector);
        static::assertSame($followingSelectors, $expr->followingSelectors);
        static::assertSame($open, $expr->openingBrace);
        static::assertSame($initializers, $expr->initializers);
        static::assertSame($close, $expr->closingBrace);
        static::assertSame(ExpressionKind::Message, $expr->getKind());
        static::assertSame([$selector], $expr->getChildren());
        $span = $expr->getSpan();
        static::assertSame(0, $span->start);
        static::assertSame(7, $span->end);
    }
}
