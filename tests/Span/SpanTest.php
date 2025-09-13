<?php

declare(strict_types=1);

namespace Cel\Tests\Span;

use Cel\Span\Span;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\TestCase;

#[CoversClass(Span::class)]
final class SpanTest extends TestCase
{
    public function testZero(): void
    {
        $span = Span::zero();
        static::assertSame(0, $span->start);
        static::assertSame(0, $span->end);
        static::assertTrue($span->isZero());

        static::assertFalse(new Span(0, 1)->isZero());
        static::assertFalse(new Span(1, 0)->isZero());
        static::assertFalse(new Span(1, 1)->isZero());
    }

    public function testConstructorAndProperties(): void
    {
        $span = new Span(10, 20);
        static::assertSame(10, $span->start);
        static::assertSame(20, $span->end);
        static::assertFalse($span->isZero());
    }

    public function testJoin(): void
    {
        $span1 = new Span(5, 10);
        $span2 = new Span(12, 18);
        $joined = $span1->join($span2);

        // Join should take the start of the first and end of the second.
        static::assertSame(5, $joined->start);
        static::assertSame(18, $joined->end);
    }

    public function testHasOffset(): void
    {
        $span = new Span(10, 20);

        static::assertTrue($span->hasOffset(10));
        static::assertTrue($span->hasOffset(15));
        static::assertFalse($span->hasOffset(20)); // End is exclusive
        static::assertFalse($span->hasOffset(9));
        static::assertFalse($span->hasOffset(21));
    }

    public function testLength(): void
    {
        static::assertSame(10, new Span(10, 20)->length());
        static::assertSame(0, new Span(5, 5)->length());
        static::assertSame(0, Span::zero()->length());
    }

    public function testIsAfter(): void
    {
        $span = new Span(10, 20);

        static::assertTrue($span->isAfter(9));
        static::assertFalse($span->isAfter(10));
        static::assertFalse($span->isAfter(15));
    }

    public function testIsBefore(): void
    {
        $span = new Span(10, 20);

        static::assertTrue($span->isBefore(20));
        static::assertTrue($span->isBefore(21));
        static::assertFalse($span->isBefore(19));
        static::assertFalse($span->isBefore(10));
    }

    public function testStringRepresentation(): void
    {
        $span = new Span(5, 15);
        static::assertSame('[5..15]', (string) $span);
    }
}
