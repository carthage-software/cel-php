<?php

declare(strict_types=1);

namespace Cel\Tests\Syntax;

use Cel\Span\Span;
use Cel\Syntax\IdentifierNode;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\Attributes\UsesClass;
use PHPUnit\Framework\TestCase;

#[CoversClass(IdentifierNode::class)]
#[UsesClass(Span::class)]
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
