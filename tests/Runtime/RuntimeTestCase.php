<?php

declare(strict_types=1);

namespace Cel\Tests\Runtime;

use Cel\CommonExpressionLanguage;
use Cel\Exception\EvaluationException;
use Cel\Runtime\Configuration;
use Cel\Runtime\Runtime;
use Cel\Runtime\RuntimeReceipt;
use Cel\Value\Value;
use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\TestCase;
use Psl\Str;

use function var_export;

abstract class RuntimeTestCase extends TestCase
{
    /**
     * @param array<string, mixed> $variables
     */
    protected function evaluate(
        string $code,
        array $variables = [],
        null|Configuration $configuration = null,
    ): RuntimeReceipt {
        $cel = new CommonExpressionLanguage(runtime: new Runtime(configuration: $configuration ?? new Configuration()));

        $expression = $cel->parseString($code);
        $expression = $cel->optimize($expression);

        return $cel->run($expression, $variables);
    }

    /**
     * @param array<string, mixed> $variables
     */
    #[DataProvider('provideEvaluationCases')]
    public function testRun(
        string $code,
        array $variables,
        Value|EvaluationException $expectedResult,
        null|Configuration $configuration = null,
    ): void {
        if ($expectedResult instanceof EvaluationException) {
            self::expectException($expectedResult::class);
            self::expectExceptionMessage($expectedResult->getMessage());
        }

        $actualResult = $this->evaluate($code, $variables, $configuration)->result;
        if (!$expectedResult instanceof Value) {
            static::fail('Expected exception of type '
            . $expectedResult::class
            . ' but got result: '
            . $actualResult::class);
        }

        static::assertInstanceOf(Value::class, $actualResult);

        static::assertTrue(
            $expectedResult->isEqual($actualResult),
            Str\format(
                "Expected result to be equal.\nExpected: %s\nActual: %s",
                var_export($expectedResult, true) ?? '',
                var_export($actualResult, true) ?? '',
            ),
        );
    }

    /**
     * @return iterable<string, array{
     *     0: string,
     *     1: array<string, mixed>,
     *     2: Value|EvaluationException,
     *     3?: null|Configuration
     * }>
     */
    abstract public static function provideEvaluationCases(): iterable;
}
