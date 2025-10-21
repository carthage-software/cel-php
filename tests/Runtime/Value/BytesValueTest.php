<?php

declare(strict_types=1);

namespace Cel\Tests\Runtime\Value;

use Cel\Value\BytesValue;
use PHPUnit\Framework\TestCase;

final class BytesValueTest extends TestCase
{
    public function testValue(): void
    {
        $value = new BytesValue('hello');
        static::assertSame('hello', $value->getRawValue());
        static::assertSame('bytes', $value->getType());
    }
}
