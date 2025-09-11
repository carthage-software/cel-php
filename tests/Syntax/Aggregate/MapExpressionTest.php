<?php

declare(strict_types=1);

namespace Cel\Tests\Syntax\Aggregate;

use Cel\Span\Span;
use Cel\Syntax\Aggregate\MapEntryNode;
use Cel\Syntax\Aggregate\MapExpression;
use Cel\Syntax\ExpressionKind;
use Cel\Syntax\PunctuatedSequence;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\Attributes\UsesClass;
use PHPUnit\Framework\TestCase;

#[CoversClass(MapExpression::class)]
#[UsesClass(PunctuatedSequence::class)]
#[UsesClass(Span::class)]
final class MapExpressionTest extends TestCase
{
    public function testConstructorAndGetters(): void
    {
        $open = new Span(0, 1);
        $close = new Span(1, 2);
        /** @var PunctuatedSequence<MapEntryNode> */
        $entries = new PunctuatedSequence([], []);

        $expr = new MapExpression($open, $entries, $close);

        static::assertSame($open, $expr->openingBrace);
        static::assertSame($entries, $expr->entries);
        static::assertSame($close, $expr->closingBrace);
        static::assertSame(ExpressionKind::Map, $expr->getKind());
        static::assertEmpty($expr->getChildren());
        $span = $expr->getSpan();
        static::assertSame(0, $span->start);
        static::assertSame(2, $span->end);
    }
}
