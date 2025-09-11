<?php

declare(strict_types=1);

namespace Cel\Tests\Syntax\Binary;

use Cel\Syntax\Binary\BinaryOperatorKind;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\TestCase;

#[CoversClass(BinaryOperatorKind::class)]
final class BinaryOperatorKindTest extends TestCase
{
    #[DataProvider('provideIsLogicalCases')]
    public function testIsLogical(BinaryOperatorKind $kind, bool $expected): void
    {
        static::assertSame($expected, $kind->isLogical());
    }

    public static function provideIsLogicalCases(): iterable
    {
        foreach (BinaryOperatorKind::cases() as $case) {
            $isLogical = match ($case) {
                BinaryOperatorKind::And, BinaryOperatorKind::Or => true,
                default => false,
            };
            yield $case->name => [$case, $isLogical];
        }
    }

    #[DataProvider('provideIsComparisonCases')]
    public function testIsComparison(BinaryOperatorKind $kind, bool $expected): void
    {
        static::assertSame($expected, $kind->isComparison());
    }

    public static function provideIsComparisonCases(): iterable
    {
        foreach (BinaryOperatorKind::cases() as $case) {
            $isComparison = match ($case) {
                BinaryOperatorKind::LessThan,
                BinaryOperatorKind::LessThanOrEqual,
                BinaryOperatorKind::GreaterThan,
                BinaryOperatorKind::GreaterThanOrEqual,
                BinaryOperatorKind::Equal,
                BinaryOperatorKind::NotEqual,
                BinaryOperatorKind::In,
                    => true,
                default => false,
            };
            yield $case->name => [$case, $isComparison];
        }
    }

    #[DataProvider('provideIsArithmeticCases')]
    public function testIsArithmetic(BinaryOperatorKind $kind, bool $expected): void
    {
        static::assertSame($expected, $kind->isArithmetic());
    }

    public static function provideIsArithmeticCases(): iterable
    {
        foreach (BinaryOperatorKind::cases() as $case) {
            $isArithmetic = match ($case) {
                BinaryOperatorKind::Plus,
                BinaryOperatorKind::Minus,
                BinaryOperatorKind::Multiply,
                BinaryOperatorKind::Divide,
                BinaryOperatorKind::Modulo,
                    => true,
                default => false,
            };
            yield $case->name => [$case, $isArithmetic];
        }
    }

    #[DataProvider('provideIsAdditiveCases')]
    public function testIsAdditive(BinaryOperatorKind $kind, bool $expected): void
    {
        static::assertSame($expected, $kind->isAdditive());
    }

    public static function provideIsAdditiveCases(): iterable
    {
        foreach (BinaryOperatorKind::cases() as $case) {
            $isAdditive = match ($case) {
                BinaryOperatorKind::Plus, BinaryOperatorKind::Minus => true,
                default => false,
            };
            yield $case->name => [$case, $isAdditive];
        }
    }

    #[DataProvider('provideIsMultiplicativeCases')]
    public function testIsMultiplicative(BinaryOperatorKind $kind, bool $expected): void
    {
        static::assertSame($expected, $kind->isMultiplicative());
    }

    public static function provideIsMultiplicativeCases(): iterable
    {
        foreach (BinaryOperatorKind::cases() as $case) {
            $isMultiplicative = match ($case) {
                BinaryOperatorKind::Multiply, BinaryOperatorKind::Divide, BinaryOperatorKind::Modulo => true,
                default => false,
            };
            yield $case->name => [$case, $isMultiplicative];
        }
    }
}
