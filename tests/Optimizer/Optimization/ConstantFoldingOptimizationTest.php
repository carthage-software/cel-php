<?php

declare(strict_types=1);

namespace Cel\Tests\Optimizer\Optimization;

use Cel\Extension\ExtensionInterface;
use Cel\Optimizer\Optimization\ConstantFoldingOptimization;
use Cel\Runtime\RuntimeInterface;
use Cel\Runtime\RuntimeReceipt;
use Cel\Span\Span;
use Cel\Syntax\Binary\BinaryExpression;
use Cel\Syntax\Binary\BinaryOperator;
use Cel\Syntax\Binary\BinaryOperatorKind;
use Cel\Syntax\Expression;
use Cel\Syntax\Literal\IntegerLiteralExpression;
use Cel\Syntax\Literal\StringLiteralExpression;
use Cel\Value\FloatValue;
use Cel\Value\IntegerValue;
use Cel\Value\StringValue;
use Override;
use PHPUnit\Framework\TestCase;
use RuntimeException;

use const INF;
use const NAN;

final class ConstantFoldingOptimizationTest extends TestCase
{
    public function testFoldsIdempotentConstant(): void
    {
        $result = new ConstantFoldingOptimization()->apply(self::additionOfOneAndTwo());

        static::assertInstanceOf(IntegerLiteralExpression::class, $result);
        static::assertSame(3, $result->value);
    }

    public function testDoesNotFoldNonIdempotentResult(): void
    {
        $runtime = self::runtimeReturning(new RuntimeReceipt(new IntegerValue(3), idempotent: false));

        static::assertNull(new ConstantFoldingOptimization($runtime)->apply(self::additionOfOneAndTwo()));
    }

    public function testDoesNotFoldWhenEvaluationThrows(): void
    {
        // `1 / 0` is foldable (both operands are literals) but raises a division-by-zero error,
        // so it must be left for runtime rather than folded.
        $expression = new BinaryExpression(
            new IntegerLiteralExpression(1, '1', new Span(0, 1)),
            new BinaryOperator(BinaryOperatorKind::Divide, new Span(1, 2)),
            new IntegerLiteralExpression(0, '0', new Span(2, 3)),
        );

        static::assertNull(new ConstantFoldingOptimization()->apply($expression));
    }

    public function testDoesNotFoldInfiniteDouble(): void
    {
        $runtime = self::runtimeReturning(new RuntimeReceipt(new FloatValue(INF), idempotent: true));

        static::assertNull(new ConstantFoldingOptimization($runtime)->apply(self::additionOfOneAndTwo()));
    }

    public function testDoesNotFoldNanDouble(): void
    {
        $runtime = self::runtimeReturning(new RuntimeReceipt(new FloatValue(NAN), idempotent: true));

        static::assertNull(new ConstantFoldingOptimization($runtime)->apply(self::additionOfOneAndTwo()));
    }

    public function testFoldsUsingTheProvidedRuntime(): void
    {
        $runtime = self::runtimeReturning(new RuntimeReceipt(new StringValue('folded-by-custom'), idempotent: true));

        $result = new ConstantFoldingOptimization($runtime)->apply(self::additionOfOneAndTwo());

        static::assertInstanceOf(StringLiteralExpression::class, $result);
        static::assertSame('folded-by-custom', $result->value);
    }

    private static function additionOfOneAndTwo(): BinaryExpression
    {
        return new BinaryExpression(
            new IntegerLiteralExpression(1, '1', new Span(0, 1)),
            new BinaryOperator(BinaryOperatorKind::Plus, new Span(1, 2)),
            new IntegerLiteralExpression(2, '2', new Span(2, 3)),
        );
    }

    private static function runtimeReturning(RuntimeReceipt $receipt): RuntimeInterface
    {
        return new class($receipt) implements RuntimeInterface {
            public function __construct(
                private readonly RuntimeReceipt $receipt,
            ) {}

            #[Override]
            public function run(Expression $expression, array $context = []): RuntimeReceipt
            {
                return $this->receipt;
            }

            #[Override]
            public function register(ExtensionInterface $extension): void {}

            #[Override]
            public static function default(): static
            {
                throw new RuntimeException('not supported in this test double');
            }
        };
    }
}
