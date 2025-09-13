<?php

declare(strict_types=1);

namespace Cel\Tests\Syntax;

use Cel\Syntax\ExpressionKind;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\TestCase;
use Psl\Vec;

#[CoversClass(ExpressionKind::class)]
final class ExpressionKindTest extends TestCase
{
    public function testEnumCases(): void
    {
        $cases = ExpressionKind::cases();
        $caseNames = Vec\map($cases, static fn(ExpressionKind $c): string => $c->name);

        static::assertContains('Literal', $caseNames);
        static::assertContains('Conditional', $caseNames);
        static::assertContains('Binary', $caseNames);
        static::assertContains('Unary', $caseNames);
        static::assertContains('Parenthesized', $caseNames);
        static::assertContains('StringLiteral', $caseNames);
        static::assertContains('BytesLiteral', $caseNames);
        static::assertContains('FloatLiteral', $caseNames);
        static::assertContains('IntLiteral', $caseNames);
        static::assertContains('UIntLiteral', $caseNames);
        static::assertContains('BoolLiteral', $caseNames);
        static::assertContains('NullLiteral', $caseNames);
        static::assertContains('MemberAccess', $caseNames);
        static::assertContains('Index', $caseNames);
        static::assertContains('Call', $caseNames);
        static::assertContains('List', $caseNames);
        static::assertContains('Map', $caseNames);
        static::assertContains('Message', $caseNames);
    }
}
