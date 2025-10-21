<?php

declare(strict_types=1);

namespace Cel\Tests\Syntax;

use Cel\Span\Span;
use Cel\Syntax\IdentifierNode;
use Cel\Syntax\PunctuatedSequence;
use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\TestCase;

use function iterator_to_array;

final class PunctuatedSequenceTest extends TestCase
{
    public function testGetIterator(): void
    {
        $elements = [
            new IdentifierNode('a', new Span(0, 1)),
            new IdentifierNode('b', new Span(2, 3)),
        ];
        $sequence = new PunctuatedSequence($elements, [new Span(1, 2)]);

        static::assertSame($elements, iterator_to_array($sequence->getIterator()));
    }

    public function testCount(): void
    {
        $elements = [
            new IdentifierNode('a', new Span(0, 1)),
            new IdentifierNode('b', new Span(2, 3)),
        ];
        $sequence = new PunctuatedSequence($elements, [new Span(1, 2)]);

        static::assertSame(2, $sequence->count());
    }

    #[DataProvider('provideTrailingCommaCases')]
    public function testHasTrailingComma(PunctuatedSequence $sequence, bool $expected): void
    {
        static::assertSame($expected, $sequence->hasTrailingComma());
    }

    /**
     * @return iterable<string, array{0: PunctuatedSequence, 1: bool}>
     */
    public static function provideTrailingCommaCases(): iterable
    {
        yield 'empty' => [new PunctuatedSequence([], []), false];

        yield 'no trailing' => [
            new PunctuatedSequence([
                new IdentifierNode('a', new Span(0, 1)),
                new IdentifierNode('b', new Span(2, 3)),
            ], [new Span(1, 2)]),
            false,
        ];

        yield 'with trailing' => [
            new PunctuatedSequence([
                new IdentifierNode('a', new Span(0, 1)),
                new IdentifierNode('b', new Span(3, 4)),
            ], [new Span(1, 2), new Span(4, 5)]),
            true,
        ];

        yield 'single element no trailing' => [
            new PunctuatedSequence([
                new IdentifierNode('a', new Span(0, 1)),
            ], []),
            false,
        ];

        yield 'single element with trailing' => [
            new PunctuatedSequence([
                new IdentifierNode('a', new Span(0, 1)),
            ], [new Span(1, 2)]),
            true,
        ];
    }

    #[DataProvider('provideSpanCases')]
    public function testGetSpan(null|Span $expected, PunctuatedSequence $sequence): void
    {
        $span = $sequence->getSpan();
        if (null === $expected) {
            static::assertNull($span);
            return;
        }

        static::assertSame($expected->start, $span?->start);
        static::assertSame($expected->end, $span?->end);
    }

    /**
     * @return iterable<string, array{0: Span|null, 1: PunctuatedSequence}>
     */
    public static function provideSpanCases(): iterable
    {
        yield 'empty' => [null, new PunctuatedSequence([], [])];

        yield 'single element' => [
            new Span(0, 1),
            new PunctuatedSequence([new IdentifierNode('a', new Span(0, 1))], []),
        ];

        yield 'single with trailing comma' => [
            new Span(0, 2),
            new PunctuatedSequence([new IdentifierNode('a', new Span(0, 1))], [new Span(1, 2)]),
        ];

        yield 'multiple elements' => [
            new Span(0, 3),
            new PunctuatedSequence([
                new IdentifierNode('a', new Span(0, 1)),
                new IdentifierNode('b', new Span(2, 3)),
            ], [new Span(1, 2)]),
        ];

        yield 'multiple with trailing comma' => [
            new Span(0, 4),
            new PunctuatedSequence([
                new IdentifierNode('a', new Span(0, 1)),
                new IdentifierNode('b', new Span(2, 3)),
            ], [new Span(1, 2), new Span(3, 4)]),
        ];

        yield 'comma end equals element end' => [
            new Span(0, 3),
            new PunctuatedSequence([
                new IdentifierNode('a', new Span(0, 1)),
                new IdentifierNode('b', new Span(2, 3)),
            ], [new Span(1, 2), new Span(3, 3)]),
        ];
    }
}
