<?php

declare(strict_types=1);

namespace Cel\Benchmarks;

use Cel\CommonExpressionLanguage;
use Cel\Runtime\Configuration;
use Cel\Runtime\ExecutionBackend;
use Cel\Runtime\Runtime;
use Cel\Syntax\Expression;
use PhpBench\Benchmark\Metadata\Annotations\Iterations;
use PhpBench\Benchmark\Metadata\Annotations\Revs;
use PhpBench\Benchmark\Metadata\Annotations\Warmup;
use Psl\Vec;
use Psr\SimpleCache\CacheInterface;
use Symfony\Component\Cache\Adapter\ArrayAdapter;
use Symfony\Component\Cache\Adapter\NullAdapter;
use Symfony\Component\Cache\Psr16Cache;
use Symfony\Component\ExpressionLanguage\ExpressionFunction;
use Symfony\Component\ExpressionLanguage\ExpressionLanguage as SymfonyExpressionLanguage;
use Symfony\Component\ExpressionLanguage\ParsedExpression as SymfonyExpression;

use function extract;

final class EvaluationBenchmark
{
    private const string CEL_EXPRESSION = <<<'CEL'
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

    private const string SEL_EXPRESSION = <<<'SEL'
            (account['balance'] >= transaction['withdrawal']
                || (account['overdraftProtection'] && account['overdraftLimit'] >= transaction['withdrawal'] - account['balance'])
                || false
                || (false && true)) ? toUpper(account['name']) : null
        SEL;

    private const string PHP_EXPRESSION = <<<'PHP'
            $result = ($account['balance'] >= $transaction['withdrawal']
                || ($account['overdraftProtection'] && $account['overdraftLimit'] >= $transaction['withdrawal'] - $account['balance'])
                || false
                || (false && true)) ? \mb_strtoupper($account['name']) : null;
        PHP;

    private const array ENVIRONMENT = [
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

    /**
     * @Revs(1000)
     * @Iterations(3)
     * @Warmup(2)
     */
    public function benchCelExpression(): void
    {
        $cel = new CommonExpressionLanguage();
        $expression = $cel->parseString(self::CEL_EXPRESSION);

        $_ = $cel->run($expression, self::ENVIRONMENT);
    }

    /**
     * @Revs(1000)
     * @Iterations(3)
     * @Warmup(2)
     */
    public function benchCachedCelExpression(): void
    {
        /** @var null|CacheInterface $cache */
        static $cache = null;
        $cache ??= new Psr16Cache(new ArrayAdapter());

        $cel = CommonExpressionLanguage::cached($cache);
        $expression = $cel->parseString(self::CEL_EXPRESSION);

        $_ = $cel->run($expression, self::ENVIRONMENT);
    }

    /**
     * @Revs(1000)
     * @Iterations(3)
     * @Warmup(2)
     */
    public function benchSymfonyExpression(): void
    {
        $symfony = new SymfonyExpressionLanguage(new NullAdapter());
        $symfony->addFunction(ExpressionFunction::fromPhp('mb_strtoupper', 'toUpper'));

        $_ = $symfony->evaluate(self::SEL_EXPRESSION, self::ENVIRONMENT);
    }

    /**
     * @Revs(1000)
     * @Iterations(3)
     * @Warmup(2)
     */
    public function benchCachedSymfonyExpression(): void
    {
        /**
         * @var null|SymfonyExpression $expression
         */
        static $expression = null;

        $symfony = new SymfonyExpressionLanguage(new NullAdapter());
        $symfony->addFunction(ExpressionFunction::fromPhp('mb_strtoupper', 'toUpper'));

        if (null === $expression) {
            $expression = $symfony->parse(self::SEL_EXPRESSION, Vec\keys(self::ENVIRONMENT));
        }

        $_ = $symfony->evaluate($expression, self::ENVIRONMENT);
    }

    /**
     * @Revs(1000)
     * @Iterations(3)
     * @Warmup(2)
     */
    public function benchInterpreterCelExpression(): void
    {
        $config = new Configuration(executionBackend: ExecutionBackend::Interpreter);
        $cel = new CommonExpressionLanguage(runtime: new Runtime(configuration: $config));
        $expression = $cel->parseString(self::CEL_EXPRESSION);

        $_ = $cel->run($expression, self::ENVIRONMENT);
    }

    /**
     * @Revs(1000)
     * @Iterations(3)
     * @Warmup(2)
     */
    public function benchVmCelExpression(): void
    {
        $config = new Configuration(executionBackend: ExecutionBackend::VirtualMachine);
        $cel = new CommonExpressionLanguage(runtime: new Runtime(configuration: $config));
        $expression = $cel->parseString(self::CEL_EXPRESSION);

        $_ = $cel->run($expression, self::ENVIRONMENT);
    }

    /**
     * Measures isolated interpreter execution (no parse overhead).
     *
     * @Revs(1000)
     * @Iterations(3)
     * @Warmup(2)
     */
    public function benchInterpreterExecution(): void
    {
        /** @var null|CommonExpressionLanguage $cel */
        static $cel = null;
        /** @var null|Expression $expression */
        static $expression = null;

        if (null === $cel || null === $expression) {
            $config = new Configuration(executionBackend: ExecutionBackend::Interpreter);
            $cel = new CommonExpressionLanguage(runtime: new Runtime(configuration: $config));
            $expression = $cel->parseString(self::CEL_EXPRESSION);
        }

        $_ = $cel->run($expression, self::ENVIRONMENT);
    }

    /**
     * Measures isolated VM compilation + execution (no parse overhead).
     *
     * @Revs(1000)
     * @Iterations(3)
     * @Warmup(2)
     */
    public function benchVmExecution(): void
    {
        /** @var null|CommonExpressionLanguage $cel */
        static $cel = null;
        /** @var null|Expression $expression */
        static $expression = null;

        if (null === $cel || null === $expression) {
            $config = new Configuration(executionBackend: ExecutionBackend::VirtualMachine);
            $cel = new CommonExpressionLanguage(runtime: new Runtime(configuration: $config));
            $expression = $cel->parseString(self::CEL_EXPRESSION);
        }

        $_ = $cel->run($expression, self::ENVIRONMENT);
    }

    /**
     * @Revs(1000)
     * @Iterations(3)
     * @Warmup(2)
     *
     * @mago-expect lint:no-eval
     */
    public function benchPHPExpression(): void
    {
        $env = self::ENVIRONMENT;

        extract($env);
        eval(self::PHP_EXPRESSION);
    }
}
