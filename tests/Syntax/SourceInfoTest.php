<?php

declare(strict_types=1);

namespace Cel\Tests\Syntax;

use Cel\Syntax\IdedExpr;
use Cel\Syntax\IdentExpr;
use Cel\Syntax\SourceInfo;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\Attributes\UsesClass;
use PHPUnit\Framework\TestCase;

#[CoversClass(SourceInfo::class)]
#[UsesClass(IdedExpr::class)]
#[UsesClass(IdentExpr::class)]
final class SourceInfoTest extends TestCase
{
    public function testJsonSerialize(): void
    {
        $info = new SourceInfo('v1', 'location.cel', [0, 10, 20], [1 => 5, 2 => 15], []);

        static::assertSame(
            '{"syntax_version":"v1","location":"location.cel","line_offsets":[0,10,20],"positions":{"1":5,"2":15},"extensions":[]}',
            json_encode($info),
        );
    }
}
