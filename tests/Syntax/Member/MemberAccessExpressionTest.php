<?php

declare(strict_types=1);

namespace Cel\Tests\Syntax\Member;

use Cel\Span\Span;
use Cel\Syntax\ExpressionKind;
use Cel\Syntax\IdentifierNode;
use Cel\Syntax\Member\IdentifierExpression;
use Cel\Syntax\Member\MemberAccessExpression;
use Cel\Syntax\SelectorNode;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\Attributes\UsesClass;
use PHPUnit\Framework\TestCase;

#[CoversClass(MemberAccessExpression::class)]
#[UsesClass(IdentifierNode::class)]
#[UsesClass(SelectorNode::class)]
#[UsesClass(Span::class)]
#[UsesClass(IdentifierExpression::class)]
final class MemberAccessExpressionTest extends TestCase
{
    public function testConstructorAndGetters(): void
    {
        $operand = new IdentifierExpression(new IdentifierNode('obj', new Span(0, 3)));
        $dot = new Span(3, 4);
        $field = new SelectorNode('field', new Span(4, 9));

        $expr = new MemberAccessExpression($operand, $dot, $field);

        static::assertSame($operand, $expr->operand);
        static::assertSame($dot, $expr->dot);
        static::assertSame($field, $expr->field);
        static::assertSame(ExpressionKind::MemberAccess, $expr->getKind());
        static::assertSame([$operand, $field], $expr->getChildren());
        $span = $expr->getSpan();
        static::assertSame(0, $span->start);
        static::assertSame(9, $span->end);
    }
}
