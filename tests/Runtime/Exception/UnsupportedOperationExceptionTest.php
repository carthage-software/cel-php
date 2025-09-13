<?php

declare(strict_types=1);

namespace Cel\Tests\Runtime\Exception;

use Cel\Runtime\Exception\UnsupportedOperationException;
use Cel\Span\Span;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\Attributes\UsesClass;
use PHPUnit\Framework\TestCase;

#[CoversClass(UnsupportedOperationException::class)]
#[UsesClass(Span::class)]
final class UnsupportedOperationExceptionTest extends TestCase
{
    public function testException(): void
    {
        $span = new Span(10, 20);
        $exception = new UnsupportedOperationException('Unsupported operation', $span);

        static::assertSame('Unsupported operation', $exception->getMessage());
        static::assertSame($span, $exception->getSpan());
    }
}
