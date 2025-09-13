<?php

declare(strict_types=1);

namespace Cel\Tests\Runtime\Exception;

use Cel\Runtime\Exception\NoSuchFunctionException;
use Cel\Span\Span;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\Attributes\UsesClass;
use PHPUnit\Framework\TestCase;

#[CoversClass(NoSuchFunctionException::class)]
#[UsesClass(Span::class)]
final class NoSuchFunctionExceptionTest extends TestCase
{
    public function testException(): void
    {
        $span = new Span(10, 20);
        $exception = new NoSuchFunctionException('No such function', $span);

        static::assertSame('No such function', $exception->getMessage());
        static::assertSame($span, $exception->getSpan());
    }
}
