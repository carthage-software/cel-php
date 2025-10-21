<?php

declare(strict_types=1);

namespace Cel\Tests\Runtime\Exception;

use Cel\Exception\NoSuchFunctionException;
use Cel\Span\Span;
use PHPUnit\Framework\TestCase;

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
