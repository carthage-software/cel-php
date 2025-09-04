<?php

declare(strict_types=1);

namespace Cel\Tests\Token;

use Cel\Span\Span;
use Cel\Token\Token;
use Cel\Token\TokenKind;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\Attributes\UsesClass;
use PHPUnit\Framework\TestCase;

#[CoversClass(Token::class)]
#[UsesClass(Span::class)]
#[UsesClass(TokenKind::class)]
final class TokenTest extends TestCase
{
    public function testConstructorAndProperties(): void
    {
        $span = new Span(0, 5);
        $kind = TokenKind::Identifier;
        $value = 'hello';

        $token = new Token($span, $kind, $value);

        static::assertSame($span, $token->span);
        static::assertSame($kind, $token->kind);
        static::assertSame($value, $token->value);
    }
}
