<?php

declare(strict_types=1);

namespace Cel\Tests\Runtime\Exception;

use Cel\Exception\NoSuchKeyException;
use Cel\Span\Span;
use PHPUnit\Framework\TestCase;

final class NoSuchKeyExceptionTest extends TestCase
{
    public function testException(): void
    {
        $span = new Span(10, 20);
        $exception = new NoSuchKeyException('No such key', $span);

        static::assertSame('No such key', $exception->getMessage());
        static::assertSame($span, $exception->getSpan());
    }
}
