<?php

declare(strict_types=1);

namespace Cel\Tests\Syntax\Unary;

use Cel\Syntax\Unary\UnaryOperatorKind;
use PHPUnit\Framework\TestCase;
use Psl\Vec;

final class UnaryOperatorKindTest extends TestCase
{
    public function testEnumCases(): void
    {
        $cases = UnaryOperatorKind::cases();
        $caseNames = Vec\map($cases, static fn(UnaryOperatorKind $c): string => $c->name);

        static::assertCount(2, $cases);
        static::assertContains('Negate', $caseNames);
        static::assertContains('Not', $caseNames);
    }
}
