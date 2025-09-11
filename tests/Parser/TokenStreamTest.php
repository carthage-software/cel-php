<?php

declare(strict_types=1);

namespace Cel\Tests\Parser;

use Cel\Input\Input;
use Cel\Lexer\Internal\Utils;
use Cel\Lexer\Lexer;
use Cel\Parser\Exception\UnexpectedEndOfFileException;
use Cel\Parser\Exception\UnexpectedTokenException;
use Cel\Parser\TokenStream;
use Cel\Span\Span;
use Cel\Token\Token;
use Cel\Token\TokenKind;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\Attributes\UsesClass;
use PHPUnit\Framework\TestCase;

#[CoversClass(TokenStream::class)]
#[UsesClass(Lexer::class)]
#[UsesClass(Input::class)]
#[UsesClass(Token::class)]
#[UsesClass(TokenKind::class)]
#[UsesClass(Span::class)]
#[UsesClass(UnexpectedEndOfFileException::class)]
#[UsesClass(UnexpectedTokenException::class)]
#[UsesClass(Utils::class)]
final class TokenStreamTest extends TestCase
{
    public function testConsumeAndPeek(): void
    {
        $stream = $this->createStream('a + b');

        // Peek should not advance the stream
        $peeked = $stream->peek();
        static::assertSame(TokenKind::Identifier, $peeked->kind);
        static::assertSame('a', $peeked->value);
        static::assertSame(0, $stream->cursorPosition());

        // Consume should advance the stream
        $consumed = $stream->consume();
        static::assertSame($peeked, $consumed);
        static::assertSame(1, $stream->cursorPosition());

        // Next token
        static::assertSame(TokenKind::Plus, $stream->peek()->kind);
        $stream->consume(); // Consume '+'
        static::assertSame(3, $stream->cursorPosition());

        static::assertSame(TokenKind::Identifier, $stream->peek()->kind);
        $stream->consume(); // Consume 'b'
        static::assertSame(5, $stream->cursorPosition());

        static::assertTrue($stream->hasReachedEnd());
    }

    public function testConsumeThrowsOnEndOfFile(): void
    {
        $stream = $this->createStream('a');
        $stream->consume(); // Consume 'a'

        $this->expectException(UnexpectedEndOfFileException::class);
        $stream->consume();
    }

    public function testPeekThrowsOnEndOfFile(): void
    {
        $stream = $this->createStream('a');
        $stream->consume(); // Consume 'a'

        $this->expectException(UnexpectedEndOfFileException::class);
        $stream->peek();
    }

    public function testEatSuccess(): void
    {
        $stream = $this->createStream('true && false');

        $token = $stream->eat(TokenKind::True);
        static::assertSame('true', $token->value);

        $token = $stream->eat(TokenKind::DoubleAmpersand);
        static::assertSame('&&', $token->value);

        static::assertFalse($stream->hasReachedEnd());
    }

    public function testEatThrowsOnUnexpectedToken(): void
    {
        $stream = $this->createStream('true && false');
        $stream->eat(TokenKind::True);
        $stream->eat(TokenKind::DoubleAmpersand);

        $this->expectException(UnexpectedTokenException::class);
        $this->expectExceptionMessage(
            "Unexpected token `False` with value 'false', expected one of: `DoubleAmpersand`",
        );
        $stream->eat(TokenKind::DoubleAmpersand);
    }

    public function testEatThrowsOnEndOfFile(): void
    {
        $stream = $this->createStream('true');
        $stream->consume();

        $this->expectException(UnexpectedEndOfFileException::class);
        $stream->eat(TokenKind::Identifier);
    }

    public function testLookahead(): void
    {
        $stream = $this->createStream('a + b * c');

        static::assertSame(TokenKind::Identifier, $stream->lookahead(0)?->kind);
        static::assertSame('a', $stream->lookahead(0)?->value);

        static::assertSame(TokenKind::Plus, $stream->lookahead(1)?->kind);
        static::assertSame('+', $stream->lookahead(1)?->value);

        static::assertSame(TokenKind::Identifier, $stream->lookahead(2)?->kind);
        static::assertSame('b', $stream->lookahead(2)?->value);

        static::assertSame(TokenKind::Asterisk, $stream->lookahead(3)?->kind);
        static::assertSame('*', $stream->lookahead(3)?->value);

        static::assertSame(TokenKind::Identifier, $stream->lookahead(4)?->kind);
        static::assertSame('c', $stream->lookahead(4)?->value);

        static::assertNull($stream->lookahead(5));

        // Consume a token and check again
        $stream->consume(); // Consume 'a'
        static::assertSame(TokenKind::Plus, $stream->lookahead(0)?->kind);
        static::assertSame(TokenKind::Identifier, $stream->lookahead(1)?->kind);
        static::assertSame('b', $stream->lookahead(1)?->value);
    }

    public function testIsAt(): void
    {
        $stream = $this->createStream('a + b');

        static::assertTrue($stream->isAt(TokenKind::Identifier));
        static::assertFalse($stream->isAt(TokenKind::Plus));

        $stream->consume(); // Consume 'a'

        static::assertTrue($stream->isAt(TokenKind::Plus));
        static::assertFalse($stream->isAt(TokenKind::Identifier));

        $stream->consume(); // Consume '+'
        $stream->consume(); // Consume 'b'

        static::assertFalse($stream->isAt(TokenKind::Identifier)); // At EOF
    }

    public function testTriviaIsSkippedAutomatically(): void
    {
        $stream = $this->createStream("   // comment\n\t ident ");

        static::assertFalse($stream->hasReachedEnd());

        $token = $stream->peek();
        static::assertSame(TokenKind::Identifier, $token->kind);
        static::assertSame('ident', $token->value);

        $stream->consume();
        static::assertTrue($stream->hasReachedEnd());
    }

    public function testHasReachedEndOnEmptyAndTriviaOnlyInput(): void
    {
        $streamEmpty = $this->createStream('');
        static::assertTrue($streamEmpty->hasReachedEnd());

        $streamTrivia = $this->createStream(" // only comments and whitespace\n\t ");
        static::assertTrue($streamTrivia->hasReachedEnd());
    }

    public function testCursorPositionAdvancesCorrectly(): void
    {
        $stream = $this->createStream('a + b');

        static::assertSame(0, $stream->cursorPosition());

        $stream->consume(); // 'a', span [0, 1]
        static::assertSame(1, $stream->cursorPosition());

        $stream->consume(); // '+', span [2, 3]
        static::assertSame(3, $stream->cursorPosition());

        $stream->consume(); // 'b', span [4, 5]
        static::assertSame(5, $stream->cursorPosition());
    }

    private function createStream(string $source): TokenStream
    {
        return new TokenStream(new Lexer(new Input($source)));
    }
}
