<?php

declare(strict_types=1);

namespace Cel\Tests\Input;

use Cel\Input\Exception\OutOfBoundsException;
use Cel\Input\Input;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\Attributes\UsesClass;
use PHPUnit\Framework\TestCase;

/**
 * @mago-expect lint:too-many-methods
 */
#[CoversClass(Input::class)]
#[UsesClass(OutOfBoundsException::class)]
final class InputTest extends TestCase
{
    public function testEmptyInput(): void
    {
        $input = new Input('');
        static::assertTrue($input->isEmpty());
        static::assertSame(0, $input->length());
        static::assertSame(0, $input->cursorPosition());
        static::assertTrue($input->hasReachedEnd());
        static::assertSame('', $input->consume(5));
        static::assertSame('', $input->read(5));
    }

    public function testBasicProperties(): void
    {
        $input = new Input('hello world');
        static::assertFalse($input->isEmpty());
        static::assertSame(11, $input->length());
        static::assertSame(0, $input->cursorPosition());
        static::assertFalse($input->hasReachedEnd());
    }

    public function testUtf8Support(): void
    {
        $input = new Input('你好，世界'); // "Hello, World" in Chinese
        static::assertSame(15, $input->length()); // 3 bytes per char * 4 + 3 bytes for comma
        static::assertSame('你', $input->consume(3));
        static::assertSame('好', $input->read(3));
        static::assertSame(3, $input->cursorPosition());
    }

    public function testSliceInRange(): void
    {
        $input = new Input('0123456789');
        static::assertSame('234', $input->sliceInRange(2, 5));
        static::assertSame('89', $input->sliceInRange(8, 12)); // Clamp end
        static::assertSame('', $input->sliceInRange(5, 5));
        static::assertSame('', $input->sliceInRange(6, 5));
    }

    public function testNext(): void
    {
        $input = new Input('abc');
        static::assertSame(0, $input->cursorPosition());
        $input->next();
        static::assertSame(1, $input->cursorPosition());
        $input->next();
        static::assertSame(2, $input->cursorPosition());
        $input->next();
        static::assertSame(3, $input->cursorPosition());
        static::assertTrue($input->hasReachedEnd());
        $input->next(); // Should not go past the end
        static::assertSame(3, $input->cursorPosition());
    }

    public function testSkip(): void
    {
        $input = new Input('0123456789');
        $input->skip(3);
        static::assertSame(3, $input->cursorPosition());
        static::assertSame('3', $input->read(1));
        $input->skip(100); // Skip past end
        static::assertSame(10, $input->cursorPosition());
        static::assertTrue($input->hasReachedEnd());
    }

    public function testConsume(): void
    {
        $input = new Input('0123456789');
        static::assertSame('012', $input->consume(3));
        static::assertSame(3, $input->cursorPosition());
        static::assertSame('3456789', $input->consume(100)); // Consume more than available
        static::assertSame(10, $input->cursorPosition());
        static::assertTrue($input->hasReachedEnd());
        static::assertSame('', $input->consume(1)); // Consume at end
    }

    public function testConsumeRemaining(): void
    {
        $input = new Input('0123456789');
        $input->skip(4);
        static::assertSame('456789', $input->consumeRemaining());
        static::assertTrue($input->hasReachedEnd());
        static::assertSame('', $input->consumeRemaining()); // At end
    }

    public function testConsumeUntil(): void
    {
        $input = new Input('hello--world--again');
        static::assertSame('hello', $input->consumeUntil('--'));
        static::assertSame(5, $input->cursorPosition());
        static::assertSame('--', $input->read(2));

        $input->consume(2); // consume the separator

        static::assertSame('world', $input->consumeUntil('--'));
        static::assertSame(12, $input->cursorPosition());
        static::assertSame('--', $input->read(2));

        $input->consume(2); // consume the separator

        // Not found
        static::assertSame('again', $input->consumeUntil('??'));
        static::assertTrue($input->hasReachedEnd());
    }

    public function testConsumeUntilIgnoreCase(): void
    {
        $input = new Input('find--ME--here');
        static::assertSame('find', $input->consumeUntil('--me--', true));
        static::assertSame(4, $input->cursorPosition());

        $input = new Input('find--ME--here');
        static::assertSame('find--ME--here', $input->consumeUntil('--me--'));
        static::assertSame(14, $input->cursorPosition());
    }

    public function testConsumeThrough(): void
    {
        $input = new Input('first|second|third');
        static::assertSame('first|', $input->consumeThrough('|'));
        static::assertSame(6, $input->cursorPosition());
        static::assertSame('second|', $input->consumeThrough('|'));
        static::assertSame(13, $input->cursorPosition());

        // Not found
        static::assertSame('third', $input->consumeThrough('??'));
        static::assertTrue($input->hasReachedEnd());
    }

    public function testConsumeWhitespace(): void
    {
        $input = new Input("  \t\n next");
        static::assertSame("  \t\n ", $input->consumeWhiteSpace());
        static::assertSame(5, $input->cursorPosition());
        static::assertSame('n', $input->read(1));

        // No whitespace
        static::assertSame('', $input->consumeWhiteSpace());
        static::assertSame(5, $input->cursorPosition());
    }

    public function testRead(): void
    {
        $input = new Input('01234');
        static::assertSame('012', $input->read(3));
        static::assertSame(0, $input->cursorPosition()); // Read does not advance cursor

        $input->skip(3);
        static::assertSame('34', $input->read(10)); // Read past end
        static::assertSame(3, $input->cursorPosition());

        $input->skip(10);
        static::assertSame('', $input->read(1)); // Read at end
    }

    public function testReadAt(): void
    {
        $input = new Input('01234');
        static::assertSame('3', $input->readAt(3));
        static::assertSame('0', $input->readAt(0));
        static::assertSame(0, $input->cursorPosition()); // Does not advance cursor
    }

    public function testReadAtOutOfBounds(): void
    {
        $input = new Input('01234');
        $this->expectException(OutOfBoundsException::class);
        $input->readAt(5);
    }

    public function testIsAt(): void
    {
        $input = new Input('hello world');
        static::assertTrue($input->isAt('hello'));
        static::assertFalse($input->isAt('world'));

        $input->skip(6);
        static::assertTrue($input->isAt('world'));

        static::assertTrue($input->isAt('')); // Empty string is always true

        $input->skip(100);
        static::assertFalse($input->isAt('w')); // At end
    }

    public function testIsAtIgnoreCase(): void
    {
        $input = new Input('Hello World');
        static::assertTrue($input->isAt('hello', true));
        $input->skip(6);
        static::assertTrue($input->isAt('WORLD', true));
    }

    public function testPeek(): void
    {
        $input = new Input('0123456789');
        static::assertSame('012', $input->peek(0, 3));
        static::assertSame('34', $input->peek(3, 2));
        static::assertSame(0, $input->cursorPosition()); // Peek does not advance cursor

        $input->skip(5);
        static::assertSame('56', $input->peek(0, 2));
        static::assertSame('89', $input->peek(3, 5)); // Peek past end

        static::assertSame('', $input->peek(100, 1)); // Offset out of bounds
    }

    public function testSliceInRangeAtBoundary(): void
    {
        $input = new Input('0123456789');
        static::assertSame('', $input->sliceInRange(5, 5));
    }

    public function testConsumeRemainingAtEnd(): void
    {
        $input = new Input('abc');
        $input->skip(3);
        static::assertTrue($input->hasReachedEnd());
        static::assertSame('', $input->consumeRemaining());
    }

    public function testConsumeUntilDefaultCase(): void
    {
        $input = new Input('helloWORLD');
        static::assertSame('hello', $input->consumeUntil('WORLD'));
    }

    public function testReadAtEnd(): void
    {
        $input = new Input('abc');
        $input->skip(3);
        static::assertTrue($input->hasReachedEnd());
        static::assertSame('', $input->read(1));
    }

    public function testIsAtEnd(): void
    {
        $input = new Input('abc');
        $input->skip(3);
        static::assertTrue($input->hasReachedEnd());
        static::assertFalse($input->isAt('a'));
    }

    public function testPeekAtEndBoundary(): void
    {
        $input = new Input('abc');
        static::assertSame('', $input->peek(3, 1));
    }

    public function testConsumeWhitespaceAtEnd(): void
    {
        $input = new Input('abc  ');
        $input->skip(3);
        static::assertSame('  ', $input->consumeWhiteSpace());
        static::assertTrue($input->hasReachedEnd());
    }

    public function testIsAtDefaultCase(): void
    {
        $input = new Input('aBc');
        static::assertFalse($input->isAt('ab'));
    }

    public function testSliceInRangeAtEnd(): void
    {
        $input = new Input('abc');
        static::assertSame('', $input->sliceInRange(3, 3));
        static::assertSame('', $input->sliceInRange(4, 3));
    }
}
