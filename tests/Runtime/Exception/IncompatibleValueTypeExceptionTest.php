<?php

declare(strict_types=1);

namespace Cel\Tests\Runtime\Exception;

use Cel\Runtime\Exception\IncompatibleValueTypeException;
use Cel\Span\Span;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\Attributes\UsesClass;
use PHPUnit\Framework\TestCase;

#[CoversClass(IncompatibleValueTypeException::class)]
#[UsesClass(Span::class)]
final class IncompatibleValueTypeExceptionTest extends TestCase
{
    public function testException(): void
    {
        $span = new Span(10, 20);
        $exception = new IncompatibleValueTypeException('Incompatible value type');

        static::assertSame('Incompatible value type', $exception->getMessage());
    }
}
