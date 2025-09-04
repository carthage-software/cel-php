<?php

declare(strict_types=1);

namespace Cel\Tests\Syntax;

use Cel\Syntax\ConstantLiteral\IntConstantLiteral;
use Cel\Syntax\ConstantLiteralExpr;
use Cel\Syntax\IdedExpr;
use Cel\Syntax\ListExpr;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\Attributes\UsesClass;
use PHPUnit\Framework\TestCase;

#[CoversClass(ListExpr::class)]
#[UsesClass(IdedExpr::class)]
#[UsesClass(ConstantLiteralExpr::class)]
#[UsesClass(IntConstantLiteral::class)]
final class ListExprTest extends TestCase
{
    public function testJsonSerialize(): void
    {
        $expr = new ListExpr([
            new IdedExpr(1, new ConstantLiteralExpr(new IntConstantLiteral(1))),
            new IdedExpr(2, new ConstantLiteralExpr(new IntConstantLiteral(2))),
        ]);

        static::assertSame(
            '{"list_expr":{"elements":[{"id":1,"const_expr":{"int64_value":1}},{"id":2,"const_expr":{"int64_value":2}}]}}',
            json_encode($expr),
        );
    }
}
