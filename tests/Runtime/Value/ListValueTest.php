<?php

declare(strict_types=1);

namespace Cel\Tests\Runtime\Value;

use Cel\Runtime\Value\ListValue;
use Cel\Runtime\Value\StringValue;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\Attributes\UsesClass;
use PHPUnit\Framework\TestCase;

#[CoversClass(ListValue::class)]
#[UsesClass(StringValue::class)]
final class ListValueTest extends TestCase
{
    public function testValue(): void
    {
        $value = new ListValue([new StringValue('a')]);
        static::assertSame(['a'], $value->getNativeValue());
        static::assertSame('list', $value->getType());
    }
}
