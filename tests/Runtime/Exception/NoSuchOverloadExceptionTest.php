<?php

declare(strict_types=1);

namespace Cel\Tests\Runtime\Exception;

use Cel\Runtime\Exception\NoSuchOverloadException;
use Cel\Span\Span;
use PHPUnit\Framework\TestCase;

final class NoSuchOverloadExceptionTest extends TestCase
{
    public function testException(): void
    {
        $span = new Span(10, 20);
        $exception = new NoSuchOverloadException('Invalid argument count', $span);

        static::assertSame('Invalid argument count', $exception->getMessage());
        static::assertSame($span, $exception->getSpan());
    }
}
