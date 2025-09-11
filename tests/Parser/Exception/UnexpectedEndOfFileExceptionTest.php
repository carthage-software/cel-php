<?php

declare(strict_types=1);

namespace Cel\Tests\Parser\Exception;

use Cel\Parser\Exception\UnexpectedEndOfFileException;
use Cel\Token\TokenKind;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\Attributes\UsesClass;
use PHPUnit\Framework\TestCase;

#[CoversClass(UnexpectedEndOfFileException::class)]
#[UsesClass(TokenKind::class)]
final class UnexpectedEndOfFileExceptionTest extends TestCase
{
    public function testUnexpectedEndOfFileExceptionWithoutExpectedTokens(): void
    {
        $exception = new UnexpectedEndOfFileException(123);

        static::assertSame(123, $exception->position);
        static::assertEmpty($exception->expected);
        static::assertSame('Unexpected end of file at position 123.', $exception->getMessage());
    }

    public function testUnexpectedEndOfFileExceptionWithExpectedTokens(): void
    {
        $expected = [TokenKind::Identifier, TokenKind::Plus];
        $exception = new UnexpectedEndOfFileException(456, $expected);

        static::assertSame(456, $exception->position);
        static::assertSame($expected, $exception->expected);
        static::assertSame(
            'Unexpected end of file, expected one of: `Identifier`, `Plus` at position 456.',
            $exception->getMessage(),
        );
    }
}
