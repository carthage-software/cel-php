<?php

declare(strict_types=1);

namespace Cel\Tests\Syntax\StructExpr;

use Cel\Syntax\ConstantLiteral\StringConstantLiteral;
use Cel\Syntax\ConstantLiteralExpr;
use Cel\Syntax\IdedExpr;
use Cel\Syntax\StructExpr\Entry;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\Attributes\UsesClass;
use PHPUnit\Framework\TestCase;

#[CoversClass(Entry::class)]
#[UsesClass(IdedExpr::class)]
#[UsesClass(ConstantLiteralExpr::class)]
#[UsesClass(StringConstantLiteral::class)]
final class EntryTest extends TestCase
{
    public function testJsonSerialize(): void
    {
        $entry = new Entry(
            1,
            'field',
            new IdedExpr(2, new ConstantLiteralExpr(new StringConstantLiteral('value'))),
            false,
        );

        static::assertSame(
            '{"id":1,"field_key":"field","value":{"id":2,"const_expr":{"string_value":"value"}},"optional_entry":false}',
            json_encode($entry),
        );
    }

    public function testJsonSerializeOptional(): void
    {
        $entry = new Entry(
            1,
            'field',
            new IdedExpr(2, new ConstantLiteralExpr(new StringConstantLiteral('value'))),
            true,
        );

        static::assertSame(
            '{"id":1,"field_key":"field","value":{"id":2,"const_expr":{"string_value":"value"}},"optional_entry":true}',
            json_encode($entry),
        );
    }
}
