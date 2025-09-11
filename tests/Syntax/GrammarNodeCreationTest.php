<?php

declare(strict_types=1);

namespace Cel\Tests\Syntax;

use Cel\Span\Span;
use Cel\Syntax\Aggregate\FieldInitializerNode;
use Cel\Syntax\Aggregate\ListExpression;
use Cel\Syntax\Aggregate\MapEntryNode;
use Cel\Syntax\Aggregate\MapExpression;
use Cel\Syntax\Aggregate\MessageExpression;
use Cel\Syntax\Binary\BinaryExpression;
use Cel\Syntax\Binary\BinaryOperator;
use Cel\Syntax\Binary\BinaryOperatorKind;
use Cel\Syntax\ConditionalExpression;
use Cel\Syntax\Expression;
use Cel\Syntax\IdentifierNode;
use Cel\Syntax\Literal\BoolLiteralExpression;
use Cel\Syntax\Literal\BytesLiteralExpression;
use Cel\Syntax\Literal\FloatLiteralExpression;
use Cel\Syntax\Literal\IntegerLiteralExpression;
use Cel\Syntax\Literal\NullLiteralExpression;
use Cel\Syntax\Literal\StringLiteralExpression;
use Cel\Syntax\Literal\UnsignedIntegerLiteralExpression;
use Cel\Syntax\Member\CallExpression;
use Cel\Syntax\Member\IdentifierExpression;
use Cel\Syntax\Member\IndexExpression;
use Cel\Syntax\Member\MemberAccessExpression;
use Cel\Syntax\ParenthesizedExpression;
use Cel\Syntax\PunctuatedSequence;
use Cel\Syntax\SelectorNode;
use Cel\Syntax\Unary\UnaryExpression;
use Cel\Syntax\Unary\UnaryOperator;
use Cel\Syntax\Unary\UnaryOperatorKind;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\Attributes\CoversNothing;
use PHPUnit\Framework\Attributes\UsesClass;
use PHPUnit\Framework\TestCase;

#[UsesClass(Span::class)]
#[CoversClass(IdentifierNode::class)]
#[CoversClass(SelectorNode::class)]
#[CoversClass(PunctuatedSequence::class)]
#[CoversClass(BinaryOperator::class)]
#[CoversClass(BinaryOperatorKind::class)]
#[CoversClass(UnaryOperator::class)]
#[CoversClass(UnaryOperatorKind::class)]
#[CoversClass(BoolLiteralExpression::class)]
#[CoversClass(BytesLiteralExpression::class)]
#[CoversClass(FloatLiteralExpression::class)]
#[CoversClass(IntegerLiteralExpression::class)]
#[CoversClass(NullLiteralExpression::class)]
#[CoversClass(StringLiteralExpression::class)]
#[CoversClass(UnsignedIntegerLiteralExpression::class)]
#[CoversClass(IdentifierExpression::class)]
#[CoversClass(ParenthesizedExpression::class)]
#[CoversClass(ListExpression::class)]
#[CoversClass(MapEntryNode::class)]
#[CoversClass(MapExpression::class)]
#[CoversClass(FieldInitializerNode::class)]
#[CoversClass(MessageExpression::class)]
#[CoversClass(BinaryExpression::class)]
#[CoversClass(ConditionalExpression::class)]
#[CoversClass(MemberAccessExpression::class)]
#[CoversClass(IndexExpression::class)]
#[CoversClass(CallExpression::class)]
#[CoversClass(UnaryExpression::class)]
final class GrammarNodeCreationTest extends TestCase
{
    public function testLiteralNodes(): void
    {
        $bool = new BoolLiteralExpression(true, 'true', new Span(0, 4));
        static::assertSame(4, $bool->getSpan()->length());

        $bytes = new BytesLiteralExpression('abc', 'b"abc"', new Span(0, 6));
        static::assertSame(6, $bytes->getSpan()->length());

        $float = new FloatLiteralExpression(1.23, '1.23', new Span(0, 4));
        static::assertSame(4, $float->getSpan()->length());

        $int = new IntegerLiteralExpression(123, '123', new Span(0, 3));
        static::assertSame(3, $int->getSpan()->length());

        $null = new NullLiteralExpression('null', new Span(0, 4));
        static::assertSame(4, $null->getSpan()->length());

        $string = new StringLiteralExpression('hello', '"hello"', new Span(0, 7));
        static::assertSame(7, $string->getSpan()->length());

        $uint = new UnsignedIntegerLiteralExpression(123, '123u', new Span(0, 4));
        static::assertSame(4, $uint->getSpan()->length());
    }

    public function testIdentifierExpressionNode(): void
    {
        $ident = new IdentifierExpression(new IdentifierNode('myVar', new Span(0, 5)));
        static::assertSame(5, $ident->getSpan()->length());
    }

    public function testParenthesizedExpressionNode(): void
    {
        $inner = new IdentifierExpression(new IdentifierNode('a', new Span(1, 2)));
        $expr = new ParenthesizedExpression(new Span(0, 1), $inner, new Span(2, 3));
        static::assertSame(3, $expr->getSpan()->length());
    }

    public function testListExpressionNode(): void
    {
        $el1 = new IdentifierExpression(new IdentifierNode('a', new Span(1, 2)));
        $el2 = new IdentifierExpression(new IdentifierNode('b', new Span(4, 5)));
        $elements = new PunctuatedSequence([$el1, $el2], [new Span(3, 4)]);
        $expr = new ListExpression(new Span(0, 1), $elements, new Span(5, 6));
        static::assertSame(6, $expr->getSpan()->length());
    }

    public function testMapExpressionNode(): void
    {
        $key = new StringLiteralExpression('key', '"key"', new Span(1, 4));
        $value = new IntegerLiteralExpression(1, '1', new Span(6, 7));
        $entry = new MapEntryNode($key, new Span(5, 6), $value);
        $entries = new PunctuatedSequence([$entry], []);
        $expr = new MapExpression(new Span(0, 1), $entries, new Span(7, 8));
        static::assertSame(8, $expr->getSpan()->length());
    }

    public function testMessageExpressionNode(): void
    {
        $typeName = new SelectorNode('MyMessage', new Span(0, 9));
        $field = new SelectorNode('field', new Span(10, 15));
        $value = new IntegerLiteralExpression(1, '1', new Span(17, 18));
        $initializer = new FieldInitializerNode($field, new Span(16, 17), $value);
        $initializers = new PunctuatedSequence([$initializer], []);
        /** @var PunctuatedSequence<SelectorNode> */
        $selectors = new PunctuatedSequence([], []);
        $expr = new MessageExpression(null, $typeName, $selectors, new Span(9, 10), $initializers, new Span(18, 19));
        static::assertSame(19, $expr->getSpan()->length());
    }

    public function testBinaryExpressionNode(): void
    {
        $left = new IdentifierExpression(new IdentifierNode('a', new Span(0, 1)));
        $op = new BinaryOperator(BinaryOperatorKind::Plus, new Span(1, 2));
        $right = new IdentifierExpression(new IdentifierNode('b', new Span(3, 4)));
        $expr = new BinaryExpression($left, $op, $right);
        static::assertSame(4, $expr->getSpan()->length());
    }

    public function testConditionalExpressionNode(): void
    {
        $cond = new IdentifierExpression(new IdentifierNode('a', new Span(0, 1)));
        $q = new Span(1, 2);
        $then = new IdentifierExpression(new IdentifierNode('b', new Span(2, 3)));
        $c = new Span(3, 4);
        $else = new IdentifierExpression(new IdentifierNode('c', new Span(4, 5)));
        $expr = new ConditionalExpression($cond, $q, $then, $c, $else);
        static::assertSame(5, $expr->getSpan()->length());
    }

    public function testMemberAccessExpressionNode(): void
    {
        $operand = new IdentifierExpression(new IdentifierNode('obj', new Span(0, 3)));
        $dot = new Span(3, 4);
        $field = new SelectorNode('prop', new Span(4, 8));
        $expr = new MemberAccessExpression($operand, $dot, $field);
        static::assertSame(8, $expr->getSpan()->length());
    }

    public function testIndexExpressionNode(): void
    {
        $operand = new IdentifierExpression(new IdentifierNode('arr', new Span(0, 3)));
        $open = new Span(3, 4);
        $index = new IntegerLiteralExpression(0, '0', new Span(4, 5));
        $close = new Span(5, 6);
        $expr = new IndexExpression($operand, $open, $index, $close);
        static::assertSame(6, $expr->getSpan()->length());
    }

    public function testCallExpressionNode(): void
    {
        $target = new IdentifierExpression(new IdentifierNode('obj', new Span(0, 3)));
        $targetSeparator = new Span(3, 4);
        $function = new SelectorNode('method', new Span(4, 10));
        $open = new Span(10, 11);
        $arg1 = new IdentifierExpression(new IdentifierNode('a', new Span(11, 12)));
        $args = new PunctuatedSequence([$arg1], []);
        $close = new Span(12, 13);
        $expr = new CallExpression($target, $targetSeparator, $function, $open, $args, $close);
        static::assertSame(13, $expr->getSpan()->length());
    }

    public function testUnaryExpressionNode(): void
    {
        $op = new UnaryOperator(UnaryOperatorKind::Not, new Span(0, 1));
        $operand = new IdentifierExpression(new IdentifierNode('a', new Span(1, 2)));
        $expr = new UnaryExpression($op, $operand);
        static::assertSame(2, $expr->getSpan()->length());
    }
}
