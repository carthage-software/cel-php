<?php

declare(strict_types=1);

namespace Cel\Tests\Syntax;

use Cel\Syntax\CallExpr;
use Cel\Syntax\ConstantLiteral\StringConstantLiteral;
use Cel\Syntax\ConstantLiteralExpr;
use Cel\Syntax\IdedExpr;
use Cel\Syntax\IdentExpr;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\Attributes\UsesClass;
use PHPUnit\Framework\TestCase;

#[CoversClass(CallExpr::class)]
#[UsesClass(IdedExpr::class)]
#[UsesClass(IdentExpr::class)]
#[UsesClass(ConstantLiteralExpr::class)]
#[UsesClass(StringConstantLiteral::class)]
final class CallExprTest extends TestCase
{
    public function testJsonSerialize(): void
    {
        $expr = new CallExpr(new IdedExpr(1, new IdentExpr('target')), 'function', [
            new IdedExpr(2, new ConstantLiteralExpr(new StringConstantLiteral('arg1'))),
        ]);

        static::assertSame(
            '{"call_expr":{"target":{"id":1,"ident_expr":{"name":"target"}},"function":"function","args":[{"id":2,"const_expr":{"string_value":"arg1"}}]}}',
            json_encode($expr),
        );
    }

    public function testJsonSerializeNoTarget(): void
    {
        $expr = new CallExpr(null, 'function', [
            new IdedExpr(1, new ConstantLiteralExpr(new StringConstantLiteral('arg1'))),
        ]);

        static::assertSame(
            '{"call_expr":{"target":null,"function":"function","args":[{"id":1,"const_expr":{"string_value":"arg1"}}]}}',
            json_encode($expr),
        );
    }
}
