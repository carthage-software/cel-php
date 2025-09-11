<?php

declare(strict_types=1);

namespace Cel\Tests\Parser\Exception;

use Cel\Parser\Exception\UnexpectedTokenException;
use Cel\Span\Span;
use Cel\Token\Token;
use Cel\Token\TokenKind;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\Attributes\UsesClass;
use PHPUnit\Framework\TestCase;

#[CoversClass(UnexpectedTokenException::class)]
#[UsesClass(Token::class)]
#[UsesClass(TokenKind::class)]
#[UsesClass(Span::class)]
final class UnexpectedTokenExceptionTest extends TestCase
{
    public function testUnexpectedTokenExceptionWithoutExpectedTokens(): void
    {
        $foundToken = new Token(new Span(10, 15), TokenKind::False, 'false');
        $exception = new UnexpectedTokenException($foundToken);

        static::assertSame($foundToken, $exception->found);
        static::assertEmpty($exception->expected);
        static::assertSame("Unexpected token `False` with value 'false' at span [10, 15].", $exception->getMessage());
    }

    public function testUnexpectedTokenExceptionWithExpectedTokens(): void
    {
        $foundToken = new Token(new Span(20, 21), TokenKind::Plus, '+');
        $expected = [TokenKind::LiteralInt, TokenKind::Identifier];
        $exception = new UnexpectedTokenException($foundToken, $expected);

        static::assertSame($foundToken, $exception->found);
        static::assertSame($expected, $exception->expected);
        static::assertSame(
            "Unexpected token `Plus` with value '+', expected one of: `LiteralInt`, `Identifier` at span [20, 21].",
            $exception->getMessage(),
        );
    }
}
