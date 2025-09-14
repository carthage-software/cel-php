<?php

declare(strict_types=1);

namespace Cel\Tests\Runtime\Value;

use Cel\Runtime\Value\ListValue;
use Cel\Runtime\Value\StringValue;
use PHPUnit\Framework\TestCase;

final class ListValueTest extends TestCase
{
    public function testValue(): void
    {
        $value = new ListValue([new StringValue('a')]);
        static::assertSame(['a'], $value->getNativeValue());
        static::assertSame('list', $value->getType());
    }
}
