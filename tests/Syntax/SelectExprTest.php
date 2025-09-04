<?php

declare(strict_types=1);

namespace Cel\Tests\Syntax;

use Cel\Syntax\IdedExpr;
use Cel\Syntax\IdentExpr;
use Cel\Syntax\SelectExpr;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\Attributes\UsesClass;
use PHPUnit\Framework\TestCase;

#[CoversClass(SelectExpr::class)]
#[UsesClass(IdedExpr::class)]
#[UsesClass(IdentExpr::class)]
final class SelectExprTest extends TestCase
{
    public function testJsonSerialize(): void
    {
        $expr = new SelectExpr(new IdedExpr(1, new IdentExpr('operand')), 'field', false);

        static::assertSame(
            '{"select_expr":{"operand":{"id":1,"ident_expr":{"name":"operand"}},"field":"field","test_only":false}}',
            json_encode($expr),
        );
    }

    public function testJsonSerializeTestOnly(): void
    {
        $expr = new SelectExpr(new IdedExpr(1, new IdentExpr('operand')), 'field', true);

        static::assertSame(
            '{"select_expr":{"operand":{"id":1,"ident_expr":{"name":"operand"}},"field":"field","test_only":true}}',
            json_encode($expr),
        );
    }
}
