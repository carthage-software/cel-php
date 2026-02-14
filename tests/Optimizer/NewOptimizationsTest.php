<?php

declare(strict_types=1);

namespace Cel\Tests\Optimizer;

use Cel\CommonExpressionLanguage;
use Cel\Optimizer\Optimization\ConditionalSimplificationOptimization;
use Cel\Optimizer\Optimization\ConstantFoldingOptimization;
use Cel\Optimizer\Optimization\DoubleNegationOptimization;
use Cel\Optimizer\Optimization\IdentityOperationOptimization;
use Cel\Optimizer\Optimizer;
use Cel\Parser\Parser;
use PHPUnit\Framework\TestCase;

final class NewOptimizationsTest extends TestCase
{
    public function testConstantFoldingOptimization(): void
    {
        $cel = CommonExpressionLanguage::default();

        // Test arithmetic constant folding
        $expr = $cel->parseString('1 + 2 * 3');
        $receipt = $cel->run($expr);
        static::assertSame(7, $receipt->result->getRawValue());

        // Test nested constant folding
        $expr = $cel->parseString('(1 + 1) * (2 + 2)');
        $receipt = $cel->run($expr);
        static::assertSame(8, $receipt->result->getRawValue());
    }

    public function testIdentityOperationOptimization(): void
    {
        $cel = CommonExpressionLanguage::default();

        // Test x + 0
        $expr = $cel->parseString('x + 0');
        $receipt = $cel->run($expr, ['x' => 5]);
        static::assertSame(5, $receipt->result->getRawValue());

        // Test x * 1
        $expr = $cel->parseString('x * 1');
        $receipt = $cel->run($expr, ['x' => 7]);
        static::assertSame(7, $receipt->result->getRawValue());

        // Test x / 1
        $expr = $cel->parseString('x / 1');
        $receipt = $cel->run($expr, ['x' => 10]);
        static::assertSame(10, $receipt->result->getRawValue());
    }

    public function testDoubleNegationOptimization(): void
    {
        $cel = CommonExpressionLanguage::default();

        // Test !!x
        $expr = $cel->parseString('!!x');
        $receipt = $cel->run($expr, ['x' => true]);
        static::assertTrue($receipt->result->getRawValue());

        $expr = $cel->parseString('!!x');
        $receipt = $cel->run($expr, ['x' => false]);
        static::assertFalse($receipt->result->getRawValue());
    }

    public function testConditionalSimplificationOptimization(): void
    {
        $cel = CommonExpressionLanguage::default();

        // Test true ? x : y
        $expr = $cel->parseString('true ? "yes" : "no"');
        $receipt = $cel->run($expr);
        static::assertSame('yes', $receipt->result->getRawValue());

        // Test false ? x : y
        $expr = $cel->parseString('false ? "yes" : "no"');
        $receipt = $cel->run($expr);
        static::assertSame('no', $receipt->result->getRawValue());
    }

    public function testComplexBenchmarkExpression(): void
    {
        $cel = CommonExpressionLanguage::default();

        $expression = <<<'CEL'
                (
                    // Multiple constant folding opportunities
                    (1 + 2 * 3 - 5) + 0 == 2 &&
                    (10 / 2 + 3 * 1) * 1 > 7 &&

                    // Identity operations
                    account.balance * 1 + 0 >= transaction.withdrawal - 0 &&

                    // Double negations and short-circuit logic
                    !!(account.overdraftProtection || false) &&
                    (true && account.overdraftLimit * 1 >= (transaction.withdrawal - account.balance) + 0) &&

                    // More constant folding
                    (5 + 5 - 3 * 2 + 1) > 3 &&

                    // Conditional simplification and string operations
                    (true ? (account.tier + "" + " customer") : "unknown") != "" &&

                    // Complex boolean expressions with constants
                    (false || (true && (account.premium || false))) &&

                    // More identity operations
                    transaction.fee / 1 + 0 < account.balance / 1 &&

                    // Nested constant expressions
                    ((1 + 1) * (2 + 2) / 2 - 3 + 1) == 2 &&

                    // String concatenation with constants
                    ("" + account.name + "" + " " + account.surname).size() > 0 &&

                    // More short-circuit logic
                    (true || account.suspended) &&
                    (account.verified && true) &&

                    // Additional constant folding in conditionals
                    (2 + 2 == 4 ? account.score : 0) * 1 >= 100 * 1 &&

                    // Complex nested arithmetic
                    ((account.deposits + 0) * 1 - (account.withdrawals - 0)) / 1 +
                    ((transaction.amount * 1 + 0) / 1) > (500 + 500 - 100 * 2) &&

                    // Final validation with multiple optimizations
                    !!(false || true) &&
                    (0 + 1 * account.balance / 1 - 0) >=
                        ((1 + 2 + 3 + 4) * 100 - (5 * 100 + 500)) + 0
                )
                ? account.name.toUpper() + " " + ("APPROVED" + "" + " " + (true ? "PREMIUM" : "STANDARD"))
                : (false ? "ERROR" : "DENIED")
            CEL;

        $environment = [
            'account' => [
                'name' => 'John',
                'surname' => 'Doe',
                'balance' => 1500,
                'overdraftProtection' => true,
                'overdraftLimit' => 2000,
                'tier' => 'gold',
                'premium' => true,
                'suspended' => false,
                'verified' => true,
                'score' => 150,
                'deposits' => 5000,
                'withdrawals' => 3000,
            ],
            'transaction' => [
                'withdrawal' => 700,
                'fee' => 5,
                'amount' => 200,
            ],
        ];

        $expr = $cel->parseString($expression);
        $receipt = $cel->run($expr, $environment);

        // The expression should evaluate to the approval message
        static::assertSame('JOHN APPROVED PREMIUM', $receipt->result->getRawValue());
    }

    public function testOptimizationsAreApplied(): void
    {
        $parser = Parser::default();
        $optimizer = Optimizer::default();
        $expression = $parser->parseString('1 + 2');

        $optimizer->addOptimization(new ConstantFoldingOptimization());
        $optimizer->addOptimization(new IdentityOperationOptimization());
        $optimizer->addOptimization(new DoubleNegationOptimization());
        $optimizer->addOptimization(new ConditionalSimplificationOptimization());

        $optimized = $optimizer->optimize($expression);
        static::assertNotSame($optimized, $expression);
    }
}
