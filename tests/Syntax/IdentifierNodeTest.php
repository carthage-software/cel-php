<?php

declare(strict_types=1);

namespace Cel\Tests\Syntax;

use Cel\Span\Span;
use Cel\Syntax\IdentifierNode;
use PHPUnit\Framework\TestCase;

final class IdentifierNodeTest extends TestCase
{
    public function testConstructorAndGetters(): void
    {
        $span = new Span(0, 4);
        $node = new IdentifierNode('test', $span);

        static::assertSame('test', $node->name);
        static::assertSame($span, $node->getSpan());
        static::assertSame($span, $node->span);
        static::assertEmpty($node->getChildren());
    }
}
