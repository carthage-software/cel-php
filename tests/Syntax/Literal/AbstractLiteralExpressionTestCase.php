<?php

declare(strict_types=1);

namespace Cel\Tests\Syntax\Literal;

use Cel\Span\Span;
use Cel\Syntax\ExpressionKind;
use Cel\Syntax\Literal\LiteralExpression;
use PHPUnit\Framework\TestCase;
use Psl\Str\Byte;

/**
 * @template T
 */
abstract class AbstractLiteralExpressionTestCase extends TestCase
{
    /**
     * @return array{0: LiteralExpression, 1: T, 2: string, 3: Span, 4: ExpressionKind}
     */
    abstract protected function createLiteral(mixed $value, string $raw, Span $span): array;

    public function testLiteral(): void
    {
        [$value, $raw] = $this->getTestValue();
        $span = new Span(0, Byte\length($raw));

        [$literal, $expectedValue, $expectedRaw, $expectedSpan, $expectedKind] = $this->createLiteral(
            $value,
            $raw,
            $span,
        );

        static::assertSame($expectedValue, $literal->getValue());
        static::assertSame($expectedRaw, $literal->getRaw());

        $span = $literal->getSpan();
        static::assertSame($expectedSpan->start, $span->start);
        static::assertSame($expectedSpan->end, $span->end);
        static::assertSame($expectedKind, $literal->getKind());
        static::assertEmpty($literal->getChildren());
    }

    /**
     * @return array{0: T, 1: string}
     */
    abstract protected function getTestValue(): array;
}
