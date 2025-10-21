<?php

declare(strict_types=1);

namespace Cel\Tests\Syntax\Aggregate;

use Cel\Span\Span;
use Cel\Syntax\Aggregate\MapEntryNode;
use Cel\Syntax\IdentifierNode;
use Cel\Syntax\Literal\StringLiteralExpression;
use Cel\Syntax\Member\IdentifierExpression;
use PHPUnit\Framework\TestCase;

final class MapEntryNodeTest extends TestCase
{
    public function testConstructorAndGetters(): void
    {
        $key = new IdentifierExpression(new IdentifierNode('key', new Span(0, 3)));
        $colon = new Span(3, 4);
        $value = new StringLiteralExpression('value', '"value"', new Span(5, 10));

        $node = new MapEntryNode($key, $colon, $value);

        static::assertSame($key, $node->key);
        static::assertSame($colon, $node->colon);
        static::assertSame($value, $node->value);
        static::assertSame([$key, $value], $node->getChildren());
        $span = $node->getSpan();
        static::assertSame(0, $span->start);
        static::assertSame(10, $span->end);
    }
}
