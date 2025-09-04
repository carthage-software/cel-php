<?php

declare(strict_types=1);

namespace Cel\Tests\Syntax;

use Cel\Syntax\ConstantLiteral\StringConstantLiteral;
use Cel\Syntax\ConstantLiteralExpr;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\Attributes\UsesClass;
use PHPUnit\Framework\TestCase;

#[CoversClass(ConstantLiteralExpr::class)]
#[UsesClass(StringConstantLiteral::class)]
final class ConstantLiteralExprTest extends TestCase
{
    public function testJsonSerialize(): void
    {
        $expr = new ConstantLiteralExpr(new StringConstantLiteral('hello'));

        static::assertSame('{"const_expr":{"string_value":"hello"}}', json_encode($expr));
    }
}
