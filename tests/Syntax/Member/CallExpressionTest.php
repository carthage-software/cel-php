<?php

declare(strict_types=1);

namespace Cel\Tests\Syntax\Member;

use Cel\Span\Span;
use Cel\Syntax\Expression;
use Cel\Syntax\ExpressionKind;
use Cel\Syntax\IdentifierNode;
use Cel\Syntax\Member\CallExpression;
use Cel\Syntax\Member\IdentifierExpression;
use Cel\Syntax\PunctuatedSequence;
use Cel\Syntax\SelectorNode;
use PHPUnit\Framework\TestCase;

final class CallExpressionTest extends TestCase
{
    public function testConstructorAndGetters(): void
    {
        $target = new IdentifierExpression(new IdentifierNode('target', new Span(0, 6)));
        $targetSeparator = new Span(6, 7);
        $function = new SelectorNode('func', new Span(7, 11));
        $open = new Span(11, 12);
        $close = new Span(12, 13);
        /** @var PunctuatedSequence<Expression> */
        $args = new PunctuatedSequence([], []);

        $expr = new CallExpression($target, $targetSeparator, $function, $open, $args, $close);

        static::assertSame($target, $expr->target);
        static::assertSame($targetSeparator, $expr->targetSeparator);
        static::assertSame($function, $expr->function);
        static::assertSame($open, $expr->openingParenthesis);
        static::assertSame($args, $expr->arguments);
        static::assertSame($close, $expr->closingParenthesis);
        static::assertSame(ExpressionKind::Call, $expr->getKind());
        static::assertSame([$function], $expr->getChildren());
        $span = $expr->getSpan();
        static::assertSame(0, $span->start);
        static::assertSame(13, $span->end);
    }

    public function testGetChildrenWithArgs(): void
    {
        $target = new IdentifierExpression(new IdentifierNode('target', new Span(0, 6)));
        $targetSeparator = new Span(6, 7);
        $function = new SelectorNode('func', new Span(7, 11));
        $open = new Span(11, 12);
        $arg1 = new IdentifierExpression(new IdentifierNode('a', new Span(12, 13)));
        $arg2 = new IdentifierExpression(new IdentifierNode('b', new Span(14, 15)));
        $args = new PunctuatedSequence([$arg1, $arg2], [new Span(13, 14)]);
        $close = new Span(15, 16);

        $expr = new CallExpression($target, $targetSeparator, $function, $open, $args, $close);

        static::assertSame([$function, $arg1, $arg2], $expr->getChildren());
    }

    public function testConstructorWithoutTarget(): void
    {
        $function = new SelectorNode('func', new Span(0, 4));
        $open = new Span(4, 5);
        $close = new Span(5, 6);
        /** @var PunctuatedSequence<Expression> */
        $args = new PunctuatedSequence([], []);

        $expr = new CallExpression(null, null, $function, $open, $args, $close);

        static::assertNull($expr->target);
        static::assertNull($expr->targetSeparator);
        static::assertSame($function, $expr->function);
        static::assertSame($open, $expr->openingParenthesis);
        static::assertSame($args, $expr->arguments);
        static::assertSame($close, $expr->closingParenthesis);
        static::assertSame(ExpressionKind::Call, $expr->getKind());
        static::assertSame([$function], $expr->getChildren());
        $span = $expr->getSpan();
        static::assertSame(0, $span->start);
        static::assertSame(6, $span->end);
    }
}
