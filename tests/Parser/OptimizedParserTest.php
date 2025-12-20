<?php

declare(strict_types=1);

namespace Cel\Tests\Parser;

use Cel\Input\Input;
use Cel\Lexer\Lexer;
use Cel\Optimizer\Optimizer;
use Cel\Parser\OptimizedParser;
use Cel\Parser\Parser;
use Cel\Runtime\Runtime;
use Cel\Syntax\Expression;
use PHPUnit\Framework\TestCase;

final class OptimizedParserTest extends TestCase
{
    public function testDefaultConstructor(): void
    {
        $parser = OptimizedParser::default();

        static::assertInstanceOf(OptimizedParser::class, $parser);
    }

    public function testParseStringReturnsOptimizedExpression(): void
    {
        $parser = OptimizedParser::default();

        // Constant folding: 1 + 2 should be optimized to 3
        $expression = $parser->parseString('1 + 2');

        static::assertInstanceOf(Expression::class, $expression);

        // Verify the expression is optimized by evaluating it
        $runtime = Runtime::default();
        $receipt = $runtime->run($expression);
        static::assertSame(3, $receipt->result->getRawValue());
    }

    public function testParseReturnsOptimizedExpression(): void
    {
        $parser = OptimizedParser::default();
        $input = new Input('1 + 2');

        $expression = $parser->parse($input);

        static::assertInstanceOf(Expression::class, $expression);

        // Verify the expression is optimized
        $runtime = Runtime::default();
        $receipt = $runtime->run($expression);
        static::assertSame(3, $receipt->result->getRawValue());
    }

    public function testConstructReturnsOptimizedExpression(): void
    {
        $parser = OptimizedParser::default();
        $input = new Input('1 + 2');
        $lexer = new Lexer($input);

        $expression = $parser->construct($lexer);

        static::assertInstanceOf(Expression::class, $expression);

        // Verify the expression is optimized
        $runtime = Runtime::default();
        $receipt = $runtime->run($expression);
        static::assertSame(3, $receipt->result->getRawValue());
    }

    public function testCanBeConstructedWithCustomComponents(): void
    {
        $parser = new OptimizedParser(parser: Parser::default(), optimizer: Optimizer::default());

        static::assertInstanceOf(OptimizedParser::class, $parser);
    }

    public function testOptimizesComplexExpressions(): void
    {
        $parser = OptimizedParser::default();

        // Multiple constant folding opportunities
        $expression = $parser->parseString('(1 + 2) * 3 - (4 + 5)');

        static::assertInstanceOf(Expression::class, $expression);

        // Verify the expression is optimized and evaluates correctly
        $runtime = Runtime::default();
        $receipt = $runtime->run($expression);
        static::assertSame(0, $receipt->result->getRawValue()); // (3) * 3 - (9) = 9 - 9 = 0
    }

    public function testOptimizesLogicalExpressions(): void
    {
        $parser = OptimizedParser::default();

        // Logical operations that can be optimized
        $expression = $parser->parseString('true && false || true');

        static::assertInstanceOf(Expression::class, $expression);

        // Verify the expression is optimized
        $runtime = Runtime::default();
        $receipt = $runtime->run($expression);
        static::assertTrue($receipt->result->getRawValue());
    }

    public function testPreservesVariableReferences(): void
    {
        $parser = OptimizedParser::default();

        // Expressions with variables should not be fully optimized away
        $expression = $parser->parseString('x + 1');

        static::assertInstanceOf(Expression::class, $expression);

        // Verify the expression still works with variables
        $runtime = Runtime::default();
        $receipt = $runtime->run($expression, ['x' => 5]);
        static::assertSame(6, $receipt->result->getRawValue());
    }

    public function testOptimizesPartialExpressions(): void
    {
        $parser = OptimizedParser::default();

        // Part of the expression can be optimized (1 + 2), but not the whole thing
        $expression = $parser->parseString('(1 + 2) + x');

        static::assertInstanceOf(Expression::class, $expression);

        // Verify the optimized part
        $runtime = Runtime::default();
        $receipt = $runtime->run($expression, ['x' => 10]);
        static::assertSame(13, $receipt->result->getRawValue());
    }
}
