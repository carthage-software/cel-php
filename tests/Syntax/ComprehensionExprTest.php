<?php

declare(strict_types=1);

namespace Cel\Tests\Syntax;

use Cel\Syntax\ComprehensionExpr;
use Cel\Syntax\ConstantLiteral\BoolConstantLiteral;
use Cel\Syntax\ConstantLiteralExpr;
use Cel\Syntax\IdedExpr;
use Cel\Syntax\IdentExpr;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\Attributes\UsesClass;
use PHPUnit\Framework\TestCase;

#[CoversClass(ComprehensionExpr::class)]
#[UsesClass(IdedExpr::class)]
#[UsesClass(IdentExpr::class)]
#[UsesClass(ConstantLiteralExpr::class)]
#[UsesClass(BoolConstantLiteral::class)]
final class ComprehensionExprTest extends TestCase
{
    public function testJsonSerialize(): void
    {
        $expr = new ComprehensionExpr(
            'iterVar',
            new IdedExpr(1, new IdentExpr('iterRange')),
            'accuVar',
            new IdedExpr(2, new IdentExpr('accuInit')),
            new IdedExpr(3, new ConstantLiteralExpr(new BoolConstantLiteral(true))),
            new IdedExpr(4, new IdentExpr('loopStep')),
            new IdedExpr(5, new IdentExpr('result')),
        );

        static::assertSame(
            '{"comprehension_expr":{"iter_var":"iterVar","iter_range":{"id":1,"ident_expr":{"name":"iterRange"}},"accu_var":"accuVar","accu_init":{"id":2,"ident_expr":{"name":"accuInit"}},"loop_condition":{"id":3,"const_expr":{"bool_value":true}},"loop_step":{"id":4,"ident_expr":{"name":"loopStep"}},"result":{"id":5,"ident_expr":{"name":"result"}}}}',
            json_encode($expr),
        );
    }
}
