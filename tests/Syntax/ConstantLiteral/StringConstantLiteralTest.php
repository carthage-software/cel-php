<?php

declare(strict_types=1);

namespace Cel\Tests\Syntax\ConstantLiteral;

use Cel\Syntax\ConstantLiteral\StringConstantLiteral;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\TestCase;

#[CoversClass(StringConstantLiteral::class)]
final class StringConstantLiteralTest extends TestCase
{
    public function testJsonSerialize(): void
    {
        $literal = new StringConstantLiteral('hello');
        static::assertSame('{"string_value":"hello"}', json_encode($literal));
    }
}
