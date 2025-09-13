<?php

declare(strict_types=1);

namespace Cel\Tests\Parser;

use Cel\Input\Input;
use Cel\Lexer\Internal\Utils;
use Cel\Lexer\Lexer;
use Cel\Parser\Exception\UnexpectedEndOfFileException;
use Cel\Parser\Exception\UnexpectedTokenException;
use Cel\Parser\Parser;
use Cel\Parser\TokenStream;
use Cel\Span\Span;
use Cel\Syntax\Aggregate\FieldInitializerNode;
use Cel\Syntax\Aggregate\ListExpression;
use Cel\Syntax\Aggregate\MapEntryNode;
use Cel\Syntax\Aggregate\MapExpression;
use Cel\Syntax\Aggregate\MessageExpression;
use Cel\Syntax\Binary\BinaryExpression;
use Cel\Syntax\Binary\BinaryOperator;
use Cel\Syntax\Binary\BinaryOperatorKind;
use Cel\Syntax\ConditionalExpression;
use Cel\Syntax\Expression;
use Cel\Syntax\IdentifierNode;
use Cel\Syntax\Literal\BoolLiteralExpression;
use Cel\Syntax\Literal\BytesLiteralExpression;
use Cel\Syntax\Literal\FloatLiteralExpression;
use Cel\Syntax\Literal\IntegerLiteralExpression;
use Cel\Syntax\Literal\NullLiteralExpression;
use Cel\Syntax\Literal\StringLiteralExpression;
use Cel\Syntax\Literal\UnsignedIntegerLiteralExpression;
use Cel\Syntax\Member\CallExpression;
use Cel\Syntax\Member\IdentifierExpression;
use Cel\Syntax\Member\IndexExpression;
use Cel\Syntax\Member\MemberAccessExpression;
use Cel\Syntax\ParenthesizedExpression;
use Cel\Syntax\PunctuatedSequence;
use Cel\Syntax\SelectorNode;
use Cel\Syntax\Unary\UnaryExpression;
use Cel\Syntax\Unary\UnaryOperator;
use Cel\Syntax\Unary\UnaryOperatorKind;
use Cel\Tests\ResourceProviderTrait;
use Cel\Token\Token;
use Cel\Token\TokenKind;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\Attributes\UsesClass;
use PHPUnit\Framework\TestCase;

#[CoversClass(Parser::class)]
#[UsesClass(Input::class)]
#[UsesClass(Lexer::class)]
#[UsesClass(TokenStream::class)]
#[UsesClass(Token::class)]
#[UsesClass(TokenKind::class)]
#[UsesClass(Span::class)]
#[UsesClass(Utils::class)]
#[UsesClass(PunctuatedSequence::class)]
#[UsesClass(FieldInitializerNode::class)]
#[UsesClass(MapEntryNode::class)]
#[UsesClass(BinaryOperator::class)]
#[UsesClass(UnaryOperator::class)]
#[UsesClass(IdentifierNode::class)]
#[UsesClass(SelectorNode::class)]
#[UsesClass(IntegerLiteralExpression::class)]
#[UsesClass(BinaryExpression::class)]
#[UsesClass(ParenthesizedExpression::class)]
#[UsesClass(UnaryExpression::class)]
#[UsesClass(IdentifierExpression::class)]
#[UsesClass(MemberAccessExpression::class)]
#[UsesClass(IndexExpression::class)]
#[UsesClass(CallExpression::class)]
#[UsesClass(ListExpression::class)]
#[UsesClass(StringLiteralExpression::class)]
#[UsesClass(BoolLiteralExpression::class)]
#[UsesClass(MapExpression::class)]
#[UsesClass(MessageExpression::class)]
#[UsesClass(ConditionalExpression::class)]
#[UsesClass(UnexpectedEndOfFileException::class)]
#[UsesClass(UnexpectedTokenException::class)]
#[UsesClass(UnsignedIntegerLiteralExpression::class)]
#[UsesClass(FloatLiteralExpression::class)]
#[UsesClass(BytesLiteralExpression::class)]
#[UsesClass(NullLiteralExpression::class)]
final class ParserTest extends TestCase
{
    use ResourceProviderTrait;

    #[DataProvider('provideCelResources')]
    public function testParsingResourcesIsSuccess(string $source): void
    {
        $parser = new Parser();
        $expression = $parser->parseString($source);

        static::assertInstanceOf(Expression::class, $expression);
    }

    /**
     * @param callable(TestCase, Expression): void $asserter
     */
    #[DataProvider('provideParseSuccessCases')]
    public function testParseSuccess(string $source, callable $asserter): void
    {
        $parser = new Parser();
        $expression = $parser->parseString($source);
        $asserter($this, $expression);
    }

    /**
     * @param class-string<\Throwable> $expectedException
     */
    #[DataProvider('provideParseErrorCases')]
    public function testParseError(string $source, string $expectedException): void
    {
        $this->expectException($expectedException);
        new Parser()->parseString($source);
    }

    public static function provideParseSuccessCases(): iterable
    {
        yield 'integer literal' =>
            [
                '123',
                function (TestCase $test, Expression $expr): void {
                    $test->assertInstanceOf(IntegerLiteralExpression::class, $expr);
                    $test->assertSame(123, $expr->value);
                },
            ];

        yield 'simple addition' =>
            [
                '1 + 2',
                function (TestCase $test, Expression $expr): void {
                    $test->assertInstanceOf(BinaryExpression::class, $expr);
                    $test->assertSame(BinaryOperatorKind::Plus, $expr->operator->kind);
                    $test->assertInstanceOf(IntegerLiteralExpression::class, $expr->left);
                    $test->assertInstanceOf(IntegerLiteralExpression::class, $expr->right);
                },
            ];

        yield 'precedence: 1 + 2 * 3' =>
            [
                '1 + 2 * 3',
                function (TestCase $test, Expression $expr): void {
                    $test->assertInstanceOf(BinaryExpression::class, $expr);
                    $test->assertSame(BinaryOperatorKind::Plus, $expr->operator->kind);
                    $test->assertInstanceOf(IntegerLiteralExpression::class, $expr->left);
                    $test->assertInstanceOf(BinaryExpression::class, $expr->right);
                    $test->assertSame(BinaryOperatorKind::Multiply, $expr->right->operator->kind);
                },
            ];

        yield 'precedence with parens: (1 + 2) * 3' =>
            [
                '(1 + 2) * 3',
                function (TestCase $test, Expression $expr): void {
                    $test->assertInstanceOf(BinaryExpression::class, $expr);
                    $test->assertSame(BinaryOperatorKind::Multiply, $expr->operator->kind);
                    $test->assertInstanceOf(ParenthesizedExpression::class, $expr->left);
                    $test->assertInstanceOf(BinaryExpression::class, $expr->left->expression);
                    $test->assertSame(BinaryOperatorKind::Plus, $expr->left->expression->operator->kind);
                },
            ];

        yield 'unary negation' =>
            [
                '-a',
                function (TestCase $test, Expression $expr): void {
                    $test->assertInstanceOf(UnaryExpression::class, $expr);
                    $test->assertSame(UnaryOperatorKind::Negate, $expr->operator->kind);
                    $test->assertInstanceOf(IdentifierExpression::class, $expr->operand);
                },
            ];

        yield 'double unary' =>
            [
                '--a',
                function (TestCase $test, Expression $expr): void {
                    $test->assertInstanceOf(UnaryExpression::class, $expr);
                    $test->assertSame(UnaryOperatorKind::Negate, $expr->operator->kind);
                    $test->assertInstanceOf(UnaryExpression::class, $expr->operand);
                    $test->assertSame(UnaryOperatorKind::Negate, $expr->operand->operator->kind);
                },
            ];

        yield 'member access' =>
            [
                'a.b.c',
                function (TestCase $test, Expression $expr): void {
                    $test->assertInstanceOf(MemberAccessExpression::class, $expr);
                    $test->assertSame('c', $expr->field->name);
                    $test->assertInstanceOf(MemberAccessExpression::class, $expr->operand);
                    $test->assertSame('b', $expr->operand->field->name);
                    $test->assertInstanceOf(IdentifierExpression::class, $expr->operand->operand);
                },
            ];

        yield 'index access' =>
            [
                'a[0]',
                function (TestCase $test, Expression $expr): void {
                    $test->assertInstanceOf(IndexExpression::class, $expr);
                    $test->assertInstanceOf(IdentifierExpression::class, $expr->operand);
                    $test->assertInstanceOf(IntegerLiteralExpression::class, $expr->index);
                },
            ];

        yield 'function call' =>
            [
                'a.b(c, 1)',
                function (TestCase $test, Expression $expr): void {
                    $test->assertInstanceOf(CallExpression::class, $expr);
                    $test->assertSame('b', $expr->function->name);
                    $test->assertInstanceOf(IdentifierExpression::class, $expr->target);
                    $test->assertCount(2, $expr->arguments->elements);
                },
            ];

        yield 'global function call' =>
            [
                'size(a)',
                function (TestCase $test, Expression $expr): void {
                    $test->assertInstanceOf(CallExpression::class, $expr);
                    $test->assertNull($expr->target);
                    $test->assertSame('size', $expr->function->name);
                    $test->assertCount(1, $expr->arguments->elements);
                },
            ];

        yield 'list literal' =>
            [
                '[1, "a", true]',
                function (TestCase $test, Expression $expr): void {
                    $test->assertInstanceOf(ListExpression::class, $expr);
                    $test->assertCount(3, $expr->elements->elements);
                    $test->assertInstanceOf(IntegerLiteralExpression::class, $expr->elements->elements[0]);
                    $test->assertInstanceOf(StringLiteralExpression::class, $expr->elements->elements[1]);
                    $test->assertInstanceOf(BoolLiteralExpression::class, $expr->elements->elements[2]);
                },
            ];

        yield 'map literal' =>
            [
                '{"a": 1, b: c}',
                function (TestCase $test, Expression $expr): void {
                    $test->assertInstanceOf(MapExpression::class, $expr);
                    $test->assertCount(2, $expr->entries->elements);
                },
            ];

        yield 'message literal' =>
            [
                'my.pkg.Message{field: "value", other: 1}',
                function (TestCase $test, Expression $expr): void {
                    $test->assertInstanceOf(MessageExpression::class, $expr);
                    $test->assertSame('Message', $expr->followingSelectors->elements[1]->name);
                    $test->assertCount(2, $expr->initializers->elements);
                },
            ];

        yield 'conditional' =>
            [
                'a ? b : c',
                function (TestCase $test, Expression $expr): void {
                    $test->assertInstanceOf(ConditionalExpression::class, $expr);
                    $test->assertInstanceOf(IdentifierExpression::class, $expr->condition);
                    $test->assertInstanceOf(IdentifierExpression::class, $expr->then);
                    $test->assertInstanceOf(IdentifierExpression::class, $expr->else);
                },
            ];

        yield 'list with trailing comma' =>
            [
                '[1, "a",]',
                function (TestCase $test, Expression $expr): void {
                    $test->assertInstanceOf(ListExpression::class, $expr);
                    $test->assertCount(2, $expr->elements->elements);
                    $test->assertTrue($expr->elements->hasTrailingComma());
                },
            ];

        yield 'map with trailing comma' =>
            [
                '{"a": 1,}',
                function (TestCase $test, Expression $expr): void {
                    $test->assertInstanceOf(MapExpression::class, $expr);
                    $test->assertCount(1, $expr->entries->elements);
                    $test->assertTrue($expr->entries->hasTrailingComma());
                },
            ];

        yield 'message with trailing comma' =>
            [
                'my.pkg.Message{field: "value",}',
                function (TestCase $test, Expression $expr): void {
                    $test->assertInstanceOf(MessageExpression::class, $expr);
                    $test->assertCount(1, $expr->initializers->elements);
                    $test->assertTrue($expr->initializers->hasTrailingComma());
                },
            ];

        yield 'call with trailing comma' =>
            [
                'my_func(1,)',
                function (TestCase $test, Expression $expr): void {
                    $test->assertInstanceOf(CallExpression::class, $expr);
                    $test->assertCount(1, $expr->arguments->elements);
                    $test->assertTrue($expr->arguments->hasTrailingComma());
                },
            ];

        yield from self::provideLiteralSuccessCases();
        yield from self::provideOperatorSuccessCases();
    }

    public static function provideLiteralSuccessCases(): iterable
    {
        yield 'uint literal' =>
            [
                '123u',
                function (TestCase $test, Expression $expr): void {
                    $test->assertInstanceOf(UnsignedIntegerLiteralExpression::class, $expr);
                    $test->assertSame(123, $expr->value);
                },
            ];

        yield 'float literal' =>
            [
                '1.23',
                function (TestCase $test, Expression $expr): void {
                    $test->assertInstanceOf(FloatLiteralExpression::class, $expr);
                    $test->assertSame(1.23, $expr->value);
                },
            ];

        yield 'bytes literal' =>
            [
                'b"abc"',
                function (TestCase $test, Expression $expr): void {
                    $test->assertInstanceOf(BytesLiteralExpression::class, $expr);
                    $test->assertSame('abc', $expr->value);
                },
            ];

        yield 'null literal' =>
            [
                'null',
                function (TestCase $test, Expression $expr): void {
                    $test->assertInstanceOf(NullLiteralExpression::class, $expr);
                },
            ];

        yield 'false literal' =>
            [
                'false',
                function (TestCase $test, Expression $expr): void {
                    $test->assertInstanceOf(BoolLiteralExpression::class, $expr);
                    $test->assertFalse($expr->value);
                },
            ];

        yield 'string literal value' =>
            [
                '"hello"',
                function (TestCase $test, Expression $expr): void {
                    $test->assertInstanceOf(StringLiteralExpression::class, $expr);
                    $test->assertSame('hello', $expr->value);
                },
            ];
    }

    public static function provideOperatorSuccessCases(): iterable
    {
        yield 'unary not' =>
            [
                '!a',
                function (TestCase $test, Expression $expr): void {
                    $test->assertInstanceOf(UnaryExpression::class, $expr);
                    $test->assertSame(UnaryOperatorKind::Not, $expr->operator->kind);
                },
            ];

        yield 'binary or' =>
            [
                'a || b',
                function (TestCase $test, Expression $expr): void {
                    $test->assertInstanceOf(BinaryExpression::class, $expr);
                    $test->assertSame(BinaryOperatorKind::Or, $expr->operator->kind);
                },
            ];

        yield 'binary and' =>
            [
                'a && b',
                function (TestCase $test, Expression $expr): void {
                    $test->assertInstanceOf(BinaryExpression::class, $expr);
                    $test->assertSame(BinaryOperatorKind::And, $expr->operator->kind);
                },
            ];

        yield 'binary not equal' =>
            [
                'a != b',
                function (TestCase $test, Expression $expr): void {
                    $test->assertInstanceOf(BinaryExpression::class, $expr);
                    $test->assertSame(BinaryOperatorKind::NotEqual, $expr->operator->kind);
                },
            ];

        yield 'binary less than' =>
            [
                'a < b',
                function (TestCase $test, Expression $expr): void {
                    $test->assertInstanceOf(BinaryExpression::class, $expr);
                    $test->assertSame(BinaryOperatorKind::LessThan, $expr->operator->kind);
                },
            ];

        yield 'binary less than or equal' =>
            [
                'a <= b',
                function (TestCase $test, Expression $expr): void {
                    $test->assertInstanceOf(BinaryExpression::class, $expr);
                    $test->assertSame(BinaryOperatorKind::LessThanOrEqual, $expr->operator->kind);
                },
            ];

        yield 'binary greater than' =>
            [
                'a > b',
                function (TestCase $test, Expression $expr): void {
                    $test->assertInstanceOf(BinaryExpression::class, $expr);
                    $test->assertSame(BinaryOperatorKind::GreaterThan, $expr->operator->kind);
                },
            ];

        yield 'binary greater than or equal' =>
            [
                'a >= b',
                function (TestCase $test, Expression $expr): void {
                    $test->assertInstanceOf(BinaryExpression::class, $expr);
                    $test->assertSame(BinaryOperatorKind::GreaterThanOrEqual, $expr->operator->kind);
                },
            ];

        yield 'binary in' =>
            [
                'a in b',
                function (TestCase $test, Expression $expr): void {
                    $test->assertInstanceOf(BinaryExpression::class, $expr);
                    $test->assertSame(BinaryOperatorKind::In, $expr->operator->kind);
                },
            ];

        yield 'binary modulo' =>
            [
                'a % b',
                function (TestCase $test, Expression $expr): void {
                    $test->assertInstanceOf(BinaryExpression::class, $expr);
                    $test->assertSame(BinaryOperatorKind::Modulo, $expr->operator->kind);
                },
            ];

        yield 'true literal' =>
            [
                'true',
                function (TestCase $test, Expression $expr): void {
                    $test->assertInstanceOf(BoolLiteralExpression::class, $expr);
                    $test->assertTrue($expr->value);
                },
            ];

        yield 'binary equals' =>
            [
                'a == b',
                function (TestCase $test, Expression $expr): void {
                    $test->assertInstanceOf(BinaryExpression::class, $expr);
                    $test->assertSame(BinaryOperatorKind::Equal, $expr->operator->kind);
                },
            ];

        yield 'binary divide' =>
            [
                'a / b',
                function (TestCase $test, Expression $expr): void {
                    $test->assertInstanceOf(BinaryExpression::class, $expr);
                    $test->assertSame(BinaryOperatorKind::Divide, $expr->operator->kind);
                },
            ];

        yield 'binary subtract' =>
            [
                'a - b',
                function (TestCase $test, Expression $expr): void {
                    $test->assertInstanceOf(BinaryExpression::class, $expr);
                    $test->assertSame(BinaryOperatorKind::Minus, $expr->operator->kind);
                },
            ];
    }

    public static function provideParseErrorCases(): iterable
    {
        yield 'unexpected token' => ['1 +', UnexpectedEndOfFileException::class];
        yield 'trailing token' => ['1 2', UnexpectedTokenException::class];
        yield 'missing closing paren' => ['(1 + 2', UnexpectedEndOfFileException::class];
        yield 'malformed map' => ['{a:1,,}', UnexpectedTokenException::class];

        yield 'empty input' => ['', UnexpectedEndOfFileException::class];
        yield 'leading dot without identifier' => ['. 1', UnexpectedTokenException::class];
        yield 'incomplete member access' => ['a.', UnexpectedEndOfFileException::class];

        yield 'invalid message name' => ['a..b', UnexpectedTokenException::class];
        yield 'incomplete message name' => ['a.b.', UnexpectedEndOfFileException::class];
    }
}
