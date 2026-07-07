<?php

declare(strict_types=1);

namespace Cel\Tests\Parser;

use Cel\Parser\Exception\UnexpectedTokenException;
use Cel\Parser\Parser;
use Cel\Syntax\Aggregate\ListExpression;
use Cel\Syntax\Aggregate\MapExpression;
use Cel\Syntax\Aggregate\MessageExpression;
use Cel\Syntax\Member\IndexExpression;
use Cel\Syntax\Member\MemberAccessExpression;
use PHPUnit\Framework\TestCase;

final class OptionalSyntaxTest extends TestCase
{
    public function testOptionalFieldSelection(): void
    {
        $expr = new Parser()->parseString('a.?b');

        static::assertInstanceOf(MemberAccessExpression::class, $expr);
        static::assertTrue($expr->isOptional());
        static::assertNotNull($expr->question);
        static::assertSame(2, $expr->question->start);
        static::assertSame('b', $expr->field->name);
    }

    public function testNonOptionalFieldSelection(): void
    {
        $expr = new Parser()->parseString('a.b');

        static::assertInstanceOf(MemberAccessExpression::class, $expr);
        static::assertFalse($expr->isOptional());
        static::assertNull($expr->question);
    }

    public function testOptionalIndex(): void
    {
        $expr = new Parser()->parseString('a[?0]');

        static::assertInstanceOf(IndexExpression::class, $expr);
        static::assertTrue($expr->isOptional());
        static::assertNotNull($expr->question);
    }

    public function testNonOptionalIndex(): void
    {
        $expr = new Parser()->parseString('a[0]');

        static::assertInstanceOf(IndexExpression::class, $expr);
        static::assertFalse($expr->isOptional());
        static::assertNull($expr->question);
    }

    public function testOptionalMapEntry(): void
    {
        $expr = new Parser()->parseString('{?"k": 1, "other": 2}');

        static::assertInstanceOf(MapExpression::class, $expr);
        static::assertTrue($expr->entries->at(0)->isOptional());
        static::assertFalse($expr->entries->at(1)->isOptional());
    }

    public function testOptionalListElement(): void
    {
        $expr = new Parser()->parseString('[a, ?b]');

        static::assertInstanceOf(ListExpression::class, $expr);
        static::assertFalse($expr->elements->at(0)->isOptional());
        static::assertTrue($expr->elements->at(1)->isOptional());
    }

    public function testOptionalFieldInitializer(): void
    {
        $expr = new Parser()->parseString('Message{?field: 1, other: 2}');

        static::assertInstanceOf(MessageExpression::class, $expr);
        static::assertTrue($expr->initializers->at(0)->isOptional());
        static::assertFalse($expr->initializers->at(1)->isOptional());
    }

    public function testOptionalSelectionIsNotAMethodCall(): void
    {
        // `a.?b(c)` is not a valid optional method call: `.?b` is an optional
        // selection, leaving `(c)` as an unexpected trailing token.
        $this->expectException(UnexpectedTokenException::class);

        new Parser()->parseString('a.?b(c)');
    }
}
