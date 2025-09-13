<?php

declare(strict_types=1);

namespace Cel\Tests\Runtime\Exception;

use Cel\Runtime\Exception\OverflowException;
use Cel\Span\Span;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\Attributes\UsesClass;
use PHPUnit\Framework\TestCase;

#[CoversClass(OverflowException::class)]
#[UsesClass(Span::class)]
final class UnsignedIntegerOverflowExceptionTest extends TestCase
{
    public function testException(): void
    {
        $span = new Span(10, 20);
        $exception = new OverflowException('Unsigned integer overflow', $span);

        static::assertSame('Unsigned integer overflow', $exception->getMessage());
        static::assertSame($span, $exception->getSpan());
    }
}
