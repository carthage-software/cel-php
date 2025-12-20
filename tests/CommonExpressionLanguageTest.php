<?php

declare(strict_types=1);

namespace Cel\Tests;

use Cel\CommonExpressionLanguage;
use Cel\Input\Input;
use Cel\Input\InputInterface;
use Cel\Lexer\Lexer;
use Cel\Lexer\LexerInterface;
use Cel\Optimizer\Optimization\OptimizationInterface;
use Cel\Optimizer\OptimizerInterface;
use Cel\Parser\ParserInterface;
use Cel\Runtime\RuntimeInterface;
use Cel\Syntax\Expression;
use Cel\Value\IntegerValue;
use PHPUnit\Framework\TestCase;
use Symfony\Component\Cache\Adapter\ArrayAdapter;
use Symfony\Component\Cache\Psr16Cache;

final class CommonExpressionLanguageTest extends TestCase
{
    public function testDefaultConstructor(): void
    {
        $cel = CommonExpressionLanguage::default();

        static::assertInstanceOf(CommonExpressionLanguage::class, $cel);
    }

    public function testParseString(): void
    {
        $cel = new CommonExpressionLanguage();

        $expression = $cel->parseString('1 + 2');

        static::assertInstanceOf(Expression::class, $expression);
    }

    public function testParse(): void
    {
        $cel = new CommonExpressionLanguage();

        $input = new Input('1 + 2');

        $expression = $cel->parse($input);

        static::assertInstanceOf(Expression::class, $expression);
    }

    public function testConstruct(): void
    {
        $cel = new CommonExpressionLanguage();

        $input = new Input('1 + 2');
        $lexer = new Lexer($input);

        $expression = $cel->construct($lexer);

        static::assertInstanceOf(Expression::class, $expression);
    }

    public function testOptimize(): void
    {
        $cel = new CommonExpressionLanguage();

        $expression = $cel->parseString('1 + 2');
        $optimized = $cel->optimize($expression);

        static::assertInstanceOf(Expression::class, $optimized);
    }

    public function testAddOptimization(): void
    {
        $cel = new CommonExpressionLanguage();

        $optimization = new class implements OptimizationInterface {
            #[\Override]
            public function apply(Expression $expression): null|Expression
            {
                return null;
            }
        };

        // Should not throw
        $cel->addOptimization($optimization);

        static::assertTrue(true);
    }

    public function testRun(): void
    {
        $cel = new CommonExpressionLanguage();

        $expression = $cel->parseString('1 + 2');

        $receipt = $cel->run($expression);

        static::assertInstanceOf(IntegerValue::class, $receipt->result);
        static::assertSame(3, $receipt->result->value);
    }

    public function testRegister(): void
    {
        $cel = new CommonExpressionLanguage();

        $extension = new class implements \Cel\Extension\ExtensionInterface {
            #[\Override]
            public function getFunctions(): array
            {
                return [];
            }

            #[\Override]
            public function getBinaryOperatorOverloads(): array
            {
                return [];
            }

            #[\Override]
            public function getUnaryOperatorOverloads(): array
            {
                return [];
            }

            #[\Override]
            public function getMessageTypes(): array
            {
                return [];
            }

            #[\Override]
            public function getValueResolvers(): array
            {
                return [];
            }
        };

        // Should not throw
        $cel->register($extension);

        static::assertTrue(true);
    }

    public function testImplementsParserInterface(): void
    {
        $cel = new CommonExpressionLanguage();

        static::assertInstanceOf(ParserInterface::class, $cel);
    }

    public function testImplementsOptimizerInterface(): void
    {
        $cel = new CommonExpressionLanguage();

        static::assertInstanceOf(OptimizerInterface::class, $cel);
    }

    public function testImplementsRuntimeInterface(): void
    {
        $cel = new CommonExpressionLanguage();

        static::assertInstanceOf(RuntimeInterface::class, $cel);
    }

    public function testCanBeConstructedWithCustomComponents(): void
    {
        $parser = $this->createMock(ParserInterface::class);
        $optimizer = $this->createMock(OptimizerInterface::class);
        $runtime = $this->createMock(RuntimeInterface::class);

        $cel = new CommonExpressionLanguage(parser: $parser, optimizer: $optimizer, runtime: $runtime);

        static::assertInstanceOf(CommonExpressionLanguage::class, $cel);
    }

    public function testParseStringDelegationToParser(): void
    {
        $expectedExpression = $this->createMock(Expression::class);

        $parser = $this->createMock(ParserInterface::class);
        $parser->expects($this->once())->method('parseString')->with('test')->willReturn($expectedExpression);

        $cel = new CommonExpressionLanguage(parser: $parser);

        $result = $cel->parseString('test');

        static::assertSame($expectedExpression, $result);
    }

    public function testParseDelegationToParser(): void
    {
        $expectedExpression = $this->createMock(Expression::class);

        $input = $this->createMock(InputInterface::class);

        $parser = $this->createMock(ParserInterface::class);
        $parser->expects($this->once())->method('parse')->with($input)->willReturn($expectedExpression);

        $cel = new CommonExpressionLanguage(parser: $parser);

        $result = $cel->parse($input);

        static::assertSame($expectedExpression, $result);
    }

    public function testConstructDelegationToParser(): void
    {
        $expectedExpression = $this->createMock(Expression::class);

        $lexer = $this->createMock(LexerInterface::class);

        $parser = $this->createMock(ParserInterface::class);
        $parser->expects($this->once())->method('construct')->with($lexer)->willReturn($expectedExpression);

        $cel = new CommonExpressionLanguage(parser: $parser);

        $result = $cel->construct($lexer);

        static::assertSame($expectedExpression, $result);
    }

    public function testOptimizeDelegationToOptimizer(): void
    {
        $inputExpression = $this->createMock(Expression::class);
        $outputExpression = $this->createMock(Expression::class);

        $optimizer = $this->createMock(OptimizerInterface::class);
        $optimizer->expects($this->once())->method('optimize')->with($inputExpression)->willReturn($outputExpression);

        $cel = new CommonExpressionLanguage(optimizer: $optimizer);

        $result = $cel->optimize($inputExpression);

        static::assertSame($outputExpression, $result);
    }

    public function testAddOptimizationDelegationToOptimizer(): void
    {
        $optimization = $this->createMock(OptimizationInterface::class);

        $optimizer = $this->createMock(OptimizerInterface::class);
        $optimizer->expects($this->once())->method('addOptimization')->with($optimization);

        $cel = new CommonExpressionLanguage(optimizer: $optimizer);

        $cel->addOptimization($optimization);
    }

    public function testCachedCreatesInstanceWithCaching(): void
    {
        $cache = new Psr16Cache(new ArrayAdapter());
        $cel = CommonExpressionLanguage::cached($cache);

        static::assertInstanceOf(CommonExpressionLanguage::class, $cel);
    }

    public function testCachedInstanceCachesParsingAndEvaluation(): void
    {
        $cache = new Psr16Cache(new ArrayAdapter());
        $cel = CommonExpressionLanguage::cached($cache);

        // First evaluation - will cache
        $expr1 = $cel->parseString('1 + 2');
        $receipt1 = $cel->run($expr1);
        static::assertSame(3, $receipt1->result->getRawValue());

        // Second evaluation - should use cache
        $expr2 = $cel->parseString('1 + 2');
        $receipt2 = $cel->run($expr2);
        static::assertSame(3, $receipt2->result->getRawValue());
    }

    public function testCachedWithCustomTtl(): void
    {
        $cache = new Psr16Cache(new ArrayAdapter());
        $cel = CommonExpressionLanguage::cached($cache, cacheTtl: 60);

        static::assertInstanceOf(CommonExpressionLanguage::class, $cel);
    }

    public function testCachedWithNullTtl(): void
    {
        $cache = new Psr16Cache(new ArrayAdapter());
        $cel = CommonExpressionLanguage::cached($cache, cacheTtl: null);

        static::assertInstanceOf(CommonExpressionLanguage::class, $cel);
    }
}
