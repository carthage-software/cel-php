<?php

declare(strict_types=1);

namespace Cel\Tests\Token;

use Cel\Token\Associativity;
use Cel\Token\Precedence;
use PHPUnit\Framework\TestCase;

final class PrecedenceTest extends TestCase
{
    public function testCallPrecedenceHasLeftToRightAssociativity(): void
    {
        $associativity = Precedence::Call->getAssociativity();

        static::assertSame(Associativity::LeftToRight, $associativity);
    }

    public function testUnaryPrecedenceHasRightToLeftAssociativity(): void
    {
        $associativity = Precedence::Unary->getAssociativity();

        static::assertSame(Associativity::RightToLeft, $associativity);
    }

    public function testMultiplicativePrecedenceHasLeftToRightAssociativity(): void
    {
        $associativity = Precedence::Multiplicative->getAssociativity();

        static::assertSame(Associativity::LeftToRight, $associativity);
    }

    public function testConditionalPrecedenceHasRightToLeftAssociativity(): void
    {
        $associativity = Precedence::Conditional->getAssociativity();

        static::assertSame(Associativity::RightToLeft, $associativity);
    }

    public function testAdditivePrecedenceHasNoAssociativity(): void
    {
        $associativity = Precedence::Additive->getAssociativity();

        static::assertNull($associativity);
    }

    public function testRelationPrecedenceHasNoAssociativity(): void
    {
        $associativity = Precedence::Relation->getAssociativity();

        static::assertNull($associativity);
    }

    public function testAndPrecedenceHasNoAssociativity(): void
    {
        $associativity = Precedence::And->getAssociativity();

        static::assertNull($associativity);
    }

    public function testOrPrecedenceHasNoAssociativity(): void
    {
        $associativity = Precedence::Or->getAssociativity();

        static::assertNull($associativity);
    }
}
