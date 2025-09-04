<?php

declare(strict_types=1);

namespace Cel\Tests\Syntax;

use Cel\Syntax\IdentExpr;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\TestCase;

#[CoversClass(IdentExpr::class)]
final class IdentExprTest extends TestCase
{
    public function testJsonSerialize(): void
    {
        $expr = new IdentExpr('name');

        static::assertSame('{"ident_expr":{"name":"name"}}', json_encode($expr));
    }
}
