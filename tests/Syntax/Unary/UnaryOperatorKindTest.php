<?php

declare(strict_types=1);

namespace Cel\Tests\Syntax\Unary;

use Cel\Syntax\Unary\UnaryOperatorKind;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\TestCase;

use function array_map;

#[CoversClass(UnaryOperatorKind::class)]
final class UnaryOperatorKindTest extends TestCase
{
    public function testEnumCases(): void
    {
        $cases = UnaryOperatorKind::cases();
        $caseNames = array_map(fn(UnaryOperatorKind $c): string => $c->name, $cases);

        static::assertCount(2, $cases);
        static::assertContains('Negate', $caseNames);
        static::assertContains('Not', $caseNames);
    }
}
