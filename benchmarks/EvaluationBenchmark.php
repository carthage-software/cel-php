<?php

declare(strict_types=1);

namespace Cel\Benchmarks;

use Cel\CommonExpressionLanguage;
use Cel\Runtime\Environment\Environment;
use Cel\Syntax\Expression;
use PhpBench\Benchmark\Metadata\Annotations\Iterations;
use PhpBench\Benchmark\Metadata\Annotations\Revs;
use PhpBench\Benchmark\Metadata\Annotations\Warmup;
use Psl\Vec;
use Symfony\Component\Cache\Adapter\NullAdapter;
use Symfony\Component\ExpressionLanguage\ExpressionFunction;
use Symfony\Component\ExpressionLanguage\ExpressionLanguage as SymfonyExpressionLanguage;
use Symfony\Component\ExpressionLanguage\ParsedExpression as SymfonyExpression;

use function extract;

final class EvaluationBenchmark
{
    private const string CEL_EXPRESSION = <<<'CEL'
        (account.balance >= transaction.withdrawal
            || (account.overdraftProtection && account.overdraftLimit >= transaction.withdrawal - account.balance)
            || false
            || (false && true)) ? account.name.toUpper() : null
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
            'name' => 'John Doe',
            'balance' => 500,
            'overdraftProtection' => true,
            'overdraftLimit' => 1000,
        ],
        'transaction' => [
            'withdrawal' => 700,
        ],
    ];

    /**
     * @Revs(5000)
     * @Iterations(20)
     * @Warmup(10)
     */
    public function benchCelExpression(): void
    {
        $cel = new CommonExpressionLanguage();
        $expression = $cel->parseString(self::CEL_EXPRESSION);

        $_ = $cel->run($expression, Environment::fromArray(self::ENVIRONMENT));
    }

    /**
     * @Revs(5000)
     * @Iterations(20)
     * @Warmup(10)
     */
    public function benchOptimizedCelExpression(): void
    {
        $cel = new CommonExpressionLanguage();
        $expression = $cel->parseString(self::CEL_EXPRESSION);
        $expression = $cel->optimize($expression);

        $_ = $cel->run($expression, Environment::fromArray(self::ENVIRONMENT));
    }

    /**
     * @Revs(5000)
     * @Iterations(20)
     * @Warmup(10)
     */
    public function benchCachedCelExpression(): void
    {
        /**
         * @var Expression|null
         */
        static $expression = null;

        $cel = new CommonExpressionLanguage();
        if (null === $expression) {
            $expression = $cel->parseString(self::CEL_EXPRESSION);
        }

        $_ = $cel->run($expression, Environment::fromArray(self::ENVIRONMENT));
    }

    /**
     * @Revs(5000)
     * @Iterations(20)
     * @Warmup(10)
     */
    public function benchCachedOptimizedCelExpression(): void
    {
        /**
         * @var Expression|null
         */
        static $expression = null;

        $cel = new CommonExpressionLanguage();
        if (null === $expression) {
            $expression = $cel->parseString(self::CEL_EXPRESSION);
            $expression = $cel->optimize($expression);
        }

        $_ = $cel->run($expression, Environment::fromArray(self::ENVIRONMENT));
    }

    /**
     * @Revs(5000)
     * @Iterations(20)
     * @Warmup(10)
     */
    public function benchSymfonyExpression(): void
    {
        $symfony = new SymfonyExpressionLanguage(new NullAdapter());
        $symfony->addFunction(ExpressionFunction::fromPhp('mb_strtoupper', 'toUpper'));

        $_ = $symfony->evaluate(self::SEL_EXPRESSION, self::ENVIRONMENT);
    }

    /**
     * @Revs(5000)
     * @Iterations(20)
     * @Warmup(10)
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
     * @Revs(5000)
     * @Iterations(20)
     * @Warmup(10)
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
