<?php

declare(strict_types=1);

namespace Cel\Tests\Syntax\Unary;

use Cel\Span\Span;
use Cel\Syntax\Unary\UnaryOperator;
use Cel\Syntax\Unary\UnaryOperatorKind;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\Attributes\UsesClass;
use PHPUnit\Framework\TestCase;

#[CoversClass(UnaryOperator::class)]
#[UsesClass(Span::class)]
#[UsesClass(UnaryOperatorKind::class)]
final class UnaryOperatorTest extends TestCase
{
    public function testConstructorAndGetters(): void
    {
        $kind = UnaryOperatorKind::Not;
        $span = new Span(0, 1);
        $op = new UnaryOperator($kind, $span);

        static::assertSame($kind, $op->kind);
        static::assertSame($span, $op->span);
        static::assertSame($span, $op->getSpan());
        static::assertEmpty($op->getChildren());
    }
}
