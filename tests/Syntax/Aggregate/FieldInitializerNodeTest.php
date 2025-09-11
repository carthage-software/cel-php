<?php

declare(strict_types=1);

namespace Cel\Tests\Syntax\Aggregate;

use Cel\Span\Span;
use Cel\Syntax\Aggregate\FieldInitializerNode;
use Cel\Syntax\IdentifierNode;
use Cel\Syntax\Literal\StringLiteralExpression;
use Cel\Syntax\SelectorNode;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\Attributes\UsesClass;
use PHPUnit\Framework\TestCase;

#[CoversClass(FieldInitializerNode::class)]
#[UsesClass(IdentifierNode::class)]
#[UsesClass(StringLiteralExpression::class)]
#[UsesClass(Span::class)]
#[UsesClass(SelectorNode::class)]
final class FieldInitializerNodeTest extends TestCase
{
    public function testConstructorAndGetters(): void
    {
        $field = new SelectorNode('field', new Span(0, 5));
        $colon = new Span(5, 6);
        $value = new StringLiteralExpression('value', '"value"', new Span(7, 12));

        $node = new FieldInitializerNode($field, $colon, $value);

        static::assertSame($field, $node->field);
        static::assertSame($colon, $node->colon);
        static::assertSame($value, $node->value);
        static::assertSame([$field, $value], $node->getChildren());
        $span = $node->getSpan();
        static::assertSame(0, $span->start);
        static::assertSame(12, $span->end);
    }
}
