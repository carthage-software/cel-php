<?php

declare(strict_types=1);

namespace Cel\Tests\Syntax;

use Cel\Syntax\IdedExpr;
use Cel\Syntax\IdentExpr;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\Attributes\UsesClass;
use PHPUnit\Framework\TestCase;

#[CoversClass(IdedExpr::class)]
#[UsesClass(IdentExpr::class)]
final class IdedExprTest extends TestCase
{
    public function testJsonSerialize(): void
    {
        $expr = new IdedExpr(1, new IdentExpr('name'));

        static::assertSame('{"id":1,"ident_expr":{"name":"name"}}', json_encode($expr));
    }
}
