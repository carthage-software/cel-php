<?php

declare(strict_types=1);

namespace Cel\Tests\Optimizer\Optimization;

use Cel\Optimizer\Optimization\OptimizationInterface;
use Cel\Syntax\Expression;
use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\TestCase;

abstract class OptimizationTestCase extends TestCase
{
    #[DataProvider('provideOptimizationCases')]
    public function testOptimization(Expression $input, null|Expression $expected): void
    {
        $result = $this->createOptimization()->apply($input);

        static::assertEquals($expected, $result);
    }

    abstract public function createOptimization(): OptimizationInterface;

    /**
     * @return iterable<array{0: Expression, 1: ?Expression}>
     */
    abstract public static function provideOptimizationCases(): iterable;
}
