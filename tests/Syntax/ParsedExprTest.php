<?php

declare(strict_types=1);

namespace Cel\Tests\Syntax;

use Cel\Syntax\IdedExpr;
use Cel\Syntax\IdentExpr;
use Cel\Syntax\ParsedExpr;
use Cel\Syntax\SourceInfo;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\Attributes\UsesClass;
use PHPUnit\Framework\TestCase;

#[CoversClass(ParsedExpr::class)]
#[UsesClass(IdedExpr::class)]
#[UsesClass(IdentExpr::class)]
#[UsesClass(SourceInfo::class)]
final class ParsedExprTest extends TestCase
{
    public function testJsonSerialize(): void
    {
        $expr = new ParsedExpr(new IdedExpr(1, new IdentExpr('name')), new SourceInfo('', '', [], [1 => 0], []));

        static::assertSame(
            '{"expr":{"id":1,"ident_expr":{"name":"name"}},"source_info":{"syntax_version":"","location":"","line_offsets":[],"positions":{"1":0},"extensions":[]}}',
            json_encode($expr),
        );
    }
}
