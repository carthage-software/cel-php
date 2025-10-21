<?php

declare(strict_types=1);

namespace Cel\Tests\Runtime\Exception;

use Cel\Exception\UnsupportedOperationException;
use Cel\Span\Span;
use PHPUnit\Framework\TestCase;

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
