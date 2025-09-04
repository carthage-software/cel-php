<?php

declare(strict_types=1);

namespace Cel\Tests\Syntax\ConstantLiteral;

use Cel\Syntax\ConstantLiteral\UintConstantLiteral;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\TestCase;

#[CoversClass(UintConstantLiteral::class)]
final class UintConstantLiteralTest extends TestCase
{
    public function testJsonSerialize(): void
    {
        $literal = new UintConstantLiteral(123);
        static::assertSame('{"uint64_value":123}', json_encode($literal));
    }
}
