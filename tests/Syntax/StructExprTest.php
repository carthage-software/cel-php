<?php

declare(strict_types=1);

namespace Cel\Tests\Syntax;

use Cel\Syntax\ConstantLiteral\StringConstantLiteral;
use Cel\Syntax\ConstantLiteralExpr;
use Cel\Syntax\IdedExpr;
use Cel\Syntax\StructExpr;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\Attributes\UsesClass;
use PHPUnit\Framework\TestCase;

#[CoversClass(StructExpr::class)]
#[UsesClass(StructExpr\Entry::class)]
#[UsesClass(IdedExpr::class)]
#[UsesClass(ConstantLiteralExpr::class)]
#[UsesClass(StringConstantLiteral::class)]
final class StructExprTest extends TestCase
{
    public function testJsonSerialize(): void
    {
        $expr = new StructExpr('typeName', [
            new StructExpr\Entry(
                1,
                'field',
                new IdedExpr(2, new ConstantLiteralExpr(new StringConstantLiteral('value'))),
                false,
            ),
        ]);

        static::assertSame(
            '{"struct_expr":{"message_name":"typeName","entries":[{"id":1,"field_key":"field","value":{"id":2,"const_expr":{"string_value":"value"}},"optional_entry":false}]}}',
            json_encode($expr),
        );
    }
}
