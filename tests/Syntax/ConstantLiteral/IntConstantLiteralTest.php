<?php

declare(strict_types=1);

namespace Cel\Tests\Syntax\ConstantLiteral;

use Cel\Syntax\ConstantLiteral\IntConstantLiteral;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\TestCase;

#[CoversClass(IntConstantLiteral::class)]
final class IntConstantLiteralTest extends TestCase
{
    public function testJsonSerialize(): void
    {
        $literal = new IntConstantLiteral(123);
        static::assertSame('{"int64_value":123}', json_encode($literal));
    }
}
