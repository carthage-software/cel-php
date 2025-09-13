<?php

declare(strict_types=1);

namespace Cel\Tests\Runtime\Exception;

use Cel\Runtime\Exception\NoSuchVariableException;
use Cel\Span\Span;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\Attributes\UsesClass;
use PHPUnit\Framework\TestCase;

#[CoversClass(NoSuchVariableException::class)]
#[UsesClass(Span::class)]
final class NoSuchVariableExceptionTest extends TestCase
{
    public function testException(): void
    {
        $span = new Span(10, 20);
        $exception = new NoSuchVariableException('No such variable', $span);

        static::assertSame('No such variable', $exception->getMessage());
        static::assertSame($span, $exception->getSpan());
    }
}
