<?php

declare(strict_types=1);

namespace Cel\Tests\Syntax\Binary;

use Cel\Span\Span;
use Cel\Syntax\Binary\BinaryOperator;
use Cel\Syntax\Binary\BinaryOperatorKind;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\Attributes\UsesClass;
use PHPUnit\Framework\TestCase;

#[CoversClass(BinaryOperator::class)]
#[UsesClass(Span::class)]
#[UsesClass(BinaryOperatorKind::class)]
final class BinaryOperatorTest extends TestCase
{
    public function testConstructorAndGetters(): void
    {
        $kind = BinaryOperatorKind::Plus;
        $span = new Span(0, 1);
        $op = new BinaryOperator($kind, $span);

        static::assertSame($kind, $op->kind);
        static::assertSame($span, $op->span);
        static::assertSame($span, $op->getSpan());
        static::assertEmpty($op->getChildren());
    }
}
