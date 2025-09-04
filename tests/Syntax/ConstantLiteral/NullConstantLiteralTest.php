<?php

declare(strict_types=1);

namespace Cel\Tests\Syntax\ConstantLiteral;

use Cel\Syntax\ConstantLiteral\NullConstantLiteral;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\TestCase;

#[CoversClass(NullConstantLiteral::class)]
final class NullConstantLiteralTest extends TestCase
{
    public function testJsonSerialize(): void
    {
        $literal = new NullConstantLiteral();
        static::assertSame('{"null_value":null}', json_encode($literal));
    }
}
