<?php

declare(strict_types=1);

namespace Cel\Tests\Syntax\ConstantLiteral;

use Cel\Syntax\ConstantLiteral\BytesConstantLiteral;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\TestCase;

#[CoversClass(BytesConstantLiteral::class)]
final class BytesConstantLiteralTest extends TestCase
{
    public function testJsonSerialize(): void
    {
        $literal = new BytesConstantLiteral(base64_encode('hello'));
        static::assertSame('{"bytes_value":"aGVsbG8="}', json_encode($literal));
    }
}
