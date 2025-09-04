<?php

declare(strict_types=1);

namespace Cel\Tests\Syntax\ConstantLiteral;

use Cel\Syntax\ConstantLiteral\BoolConstantLiteral;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\TestCase;

#[CoversClass(BoolConstantLiteral::class)]
final class BoolConstantLiteralTest extends TestCase
{
    public function testJsonSerialize(): void
    {
        $literal = new BoolConstantLiteral(true);
        static::assertSame('{"bool_value":true}', json_encode($literal));

        $literal = new BoolConstantLiteral(false);
        static::assertSame('{"bool_value":false}', json_encode($literal));
    }
}
