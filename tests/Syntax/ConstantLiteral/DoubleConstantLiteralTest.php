<?php

declare(strict_types=1);

namespace Cel\Tests\Syntax\ConstantLiteral;

use Cel\Syntax\ConstantLiteral\DoubleConstantLiteral;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\TestCase;

#[CoversClass(DoubleConstantLiteral::class)]
final class DoubleConstantLiteralTest extends TestCase
{
    public function testJsonSerialize(): void
    {
        $literal = new DoubleConstantLiteral(1.23);
        static::assertSame('{"double_value":1.23}', json_encode($literal));
    }
}
