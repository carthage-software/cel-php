<?php

declare(strict_types=1);

namespace Cel\Tests\Runtime;

use Cel\Runtime\Exception\NoSuchOverloadException;
use Cel\Runtime\Exception\OverflowException;
use Cel\Runtime\Exception\RuntimeException;
use Cel\Runtime\Interpreter\TreeWalking\TreeWalkingInterpreter;
use Cel\Runtime\Runtime;
use Cel\Runtime\Value\BooleanValue;
use Cel\Runtime\Value\FloatValue;
use Cel\Runtime\Value\IntegerValue;
use Cel\Runtime\Value\ListValue;
use Cel\Runtime\Value\StringValue;
use Cel\Runtime\Value\UnsignedIntegerValue;
use Cel\Runtime\Value\Value;
use Cel\Span\Span;
use Override;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\Attributes\Medium;

/**
 * @mago-expect lint:halstead
 */
#[CoversClass(Runtime::class)]
#[CoversClass(TreeWalkingInterpreter::class)]
#[Medium]
final class RuntimeTest extends RuntimeTestCase
{
    /**
     * @return iterable<string, array{0: string, 1: array<string, mixed>, 2: Value|RuntimeException}>
     */
    #[Override]
    public static function provideEvaluationCases(): iterable
    {
        yield 'Transaction validation: success' =>
            [
                'request.amount < account.balance + account.overdraft_limit',
                [
                    'request' => ['amount' => 50.0],
                    'account' => ['balance' => 100.0, 'overdraft_limit' => 20.0],
                ],
                new BooleanValue(true),
            ];

        yield 'Transaction validation: failure' =>
            [
                'request.amount < account.balance',
                [
                    'request' => ['amount' => 150.0],
                    'account' => ['balance' => 100.0],
                ],
                new BooleanValue(false),
            ];

        yield 'RBAC: admin access' =>
            [
                '\'admin\' in user.roles',
                [
                    'user' => ['roles' => ['editor', 'admin']],
                ],
                new BooleanValue(true),
            ];

        yield 'RBAC: owner access' =>
            [
                'user.id == resource.owner_id',
                [
                    'user' => ['id' => 'user-123'],
                    'resource' => ['owner_id' => 'user-123'],
                ],
                new BooleanValue(true),
            ];

        yield 'Error: Type mismatch in addition' =>
            [
                'user.login_attempts + \'1\'',
                [
                    'user' => ['login_attempts' => 5],
                ],
                new NoSuchOverloadException('Cannot add `int` and `string`', new Span(0, 25)),
            ];

        yield 'Error: Unsigned integer overflow' =>
            [
                'inventory.stock_count - request.quantity',
                [
                    'inventory' => ['stock_count' => new UnsignedIntegerValue(10)],
                    'request' => ['quantity' => new UnsignedIntegerValue(50)],
                ],
                new OverflowException('Unsigned integer overflow on subtraction', new Span(0, 39)),
            ];

        // Core Extension
        yield 'Core typeOf: boolean' => ['typeOf(true)', [], new StringValue('bool')];
        yield 'Core typeOf: integer' => ['typeOf(1)', [], new StringValue('int')];
        yield 'Core typeOf: float' => ['typeOf(1.0)', [], new StringValue('float')];
        yield 'Core typeOf: string' => ['typeOf("hello")', [], new StringValue('string')];
        yield 'Core typeOf: list' => ['typeOf([1, 2])', [], new StringValue('list')];
        yield 'Core typeOf: map' => ['typeOf({"a": 1})', [], new StringValue('map')];
        yield 'Core typeOf: null' => ['typeOf(null)', [], new StringValue('null')];
        yield 'Core typeOf: unsigned integer' => ['typeOf(1u)', [], new StringValue('uint')];

        // Core string
        yield 'Core string: boolean' => ['string(true)', [], new StringValue('true')];
        yield 'Core string: integer' => ['string(123)', [], new StringValue('123')];
        yield 'Core string: float' => ['string(1.23)', [], new StringValue('1.23')];
        yield 'Core string: string' => ['string("hello")', [], new StringValue('hello')];
        yield 'Core string: unsigned integer' => ['string(1u)', [], new StringValue('1')];

        // Core int
        yield 'Core int: boolean true' => ['int(true)', [], new IntegerValue(1)];
        yield 'Core int: boolean false' => ['int(false)', [], new IntegerValue(0)];
        yield 'Core int: integer' => ['int(123)', [], new IntegerValue(123)];
        yield 'Core int: float positive' => ['int(1.23)', [], new IntegerValue(1)];
        yield 'Core int: float negative' => ['int(-1.23)', [], new IntegerValue(-1)];
        yield 'Core int: string integer' => ['int("123")', [], new IntegerValue(123)];
        yield 'Core int: unsigned integer' => ['int(1u)', [], new IntegerValue(1)];

        // Core float
        yield 'Core float: boolean true' => ['float(true)', [], new FloatValue(1.0)];
        yield 'Core float: boolean false' => ['float(false)', [], new FloatValue(0.0)];
        yield 'Core float: integer' => ['float(123)', [], new FloatValue(123.0)];
        yield 'Core float: float' => ['float(1.23)', [], new FloatValue(1.23)];
        yield 'Core float: string integer' => ['float("123")', [], new FloatValue(123.0)];
        yield 'Core float: string float' => ['float("1.23")', [], new FloatValue(1.23)];
        yield 'Core float: unsigned integer' => ['float(1u)', [], new FloatValue(1.0)];

        // Core bool
        yield 'Core bool: boolean true' => ['bool(true)', [], new BooleanValue(true)];
        yield 'Core bool: boolean false' => ['bool(false)', [], new BooleanValue(false)];
        yield 'Core bool: integer non-zero' => ['bool(1)', [], new BooleanValue(true)];
        yield 'Core bool: integer zero' => ['bool(0)', [], new BooleanValue(false)];
        yield 'Core bool: float non-zero' => ['bool(1.0)', [], new BooleanValue(true)];
        yield 'Core bool: float zero' => ['bool(0.0)', [], new BooleanValue(false)];
        yield 'Core bool: unsigned integer non-zero' => ['bool(1u)', [], new BooleanValue(true)];
        yield 'Core bool: unsigned integer zero' => ['bool(0u)', [], new BooleanValue(false)];

        // Core size
        yield 'Core size: string' => ['size("hello")', [], new IntegerValue(5)];
        yield 'Core size: empty string' => ['size("")', [], new IntegerValue(0)];
        yield 'Core size: multibyte string' => ['size("你好")', [], new IntegerValue(2)];
        yield 'Core size: list' => ['size([1, 2, 3])', [], new IntegerValue(3)];
        yield 'Core size: empty list' => ['size([])', [], new IntegerValue(0)];
        yield 'Core size: map' => ['size({"a": 1, "b": 2})', [], new IntegerValue(2)];
        yield 'Core size: empty map' => ['size({})', [], new IntegerValue(0)];
        yield 'Core size: unsupported type (int)' =>
            [
                'size(123)',
                [],
                new NoSuchOverloadException(
                    'Invalid arguments for function "size". Got `(int)`, but expected one of: `(string)`, `(bytes)`, `(list)`, or `(map)`',
                    new Span(0, 7),
                ),
            ];
        yield 'Core size: unsupported type (bool)' =>
            [
                'size(true)',
                [],
                new NoSuchOverloadException(
                    'Invalid arguments for function "size". Got `(bool)`, but expected one of: `(string)`, `(bytes)`, `(list)`, or `(map)`',
                    new Span(0, 8),
                ),
            ];

        // Strings Extension
        yield 'Strings contains: found' => ['contains("hello world", "world")', [], new BooleanValue(true)];
        yield 'Strings contains: not found' => ['contains("hello world", "galaxy")', [], new BooleanValue(false)];
        yield 'Strings contains: empty substring' => ['contains("hello", "")', [], new BooleanValue(true)];
        yield 'Strings contains: identical strings' => ['contains("hello", "hello")', [], new BooleanValue(true)];
        yield 'Strings contains: substring at start' =>
            [
                'contains("hello world", "hello")',
                [],
                new BooleanValue(true),
            ];
        yield 'Strings contains: substring at end' => ['contains("hello world", "world")', [], new BooleanValue(true)];

        yield 'Strings endsWith: found' => ['endsWith("hello world", "world")', [], new BooleanValue(true)];
        yield 'Strings endsWith: not found' => ['endsWith("hello world", "hello")', [], new BooleanValue(false)];
        yield 'Strings endsWith: empty substring' => ['endsWith("hello", "")', [], new BooleanValue(true)];
        yield 'Strings endsWith: identical strings' => ['endsWith("hello", "hello")', [], new BooleanValue(true)];

        yield 'Strings startsWith: found' => ['startsWith("hello world", "hello")', [], new BooleanValue(true)];
        yield 'Strings startsWith: not found' => ['startsWith("hello world", "world")', [], new BooleanValue(false)];
        yield 'Strings startsWith: empty substring' => ['startsWith("hello", "")', [], new BooleanValue(true)];
        yield 'Strings startsWith: identical strings' => ['startsWith("hello", "hello")', [], new BooleanValue(true)];

        yield 'Strings indexOf: simple found' => ['indexOf("hello mellow", "ello")', [], new IntegerValue(1)];
        yield 'Strings indexOf: not found' => ['indexOf("hello mellow", "jello")', [], new IntegerValue(-1)];
        yield 'Strings indexOf: empty substring' => ['indexOf("hello", "")', [], new IntegerValue(0)];
        yield 'Strings indexOf: with offset' => ['indexOf("hello mellow", "ello", 2)', [], new IntegerValue(7)];
        yield 'Strings indexOf: with offset not found' =>
            [
                'indexOf("hello mellow", "ello", 8)',
                [],
                new IntegerValue(-1),
            ];
        yield 'Strings indexOf: empty substring with offset' =>
            [
                'indexOf("hello mellow", "", 2)',
                [],
                new IntegerValue(2),
            ];
        yield 'Strings indexOf: negative offset error' =>
            [
                'indexOf("hello mellow", "ello", -1)',
                [],
                new IntegerValue(-1),
            ];

        yield 'Strings lastIndexOf: simple found' => ['lastIndexOf("hello mellow", "ello")', [], new IntegerValue(7)];
        yield 'Strings lastIndexOf: not found' => ['lastIndexOf("hello mellow", "jello")', [], new IntegerValue(-1)];
        yield 'Strings lastIndexOf: empty substring' => ['lastIndexOf("hello", "")', [], new IntegerValue(5)];
        yield 'Strings lastIndexOf: with offset' => ['lastIndexOf("hello mellow", "ello", 6)', [], new IntegerValue(7)];
        yield 'Strings lastIndexOf: with offset not found' =>
            [
                'lastIndexOf("hello mellow", "ello", 0)',
                [],
                new IntegerValue(7),
            ];
        yield 'Strings lastIndexOf: empty substring with offset' =>
            [
                'lastIndexOf("hello mellow", "", 0)',
                [],
                new IntegerValue(0),
            ];
        yield 'Strings lastIndexOf: negative offset error' =>
            [
                'lastIndexOf("hello mellow", "ello", -1)',
                [],
                new IntegerValue(-1),
            ];

        yield 'Strings replace: all occurrences' =>
            [
                'replace("hello hello", "he", "we")',
                [],
                new StringValue('wello wello'),
            ];
        yield 'Strings replace: empty needle' =>
            [
                'replace("hello hello", "", "_")',
                [],
                new StringValue('_h_e_l_l_o_ _h_e_l_l_o_'),
            ];
        yield 'Strings replace: empty replacement' =>
            [
                'replace("hello hello", "h", "")',
                [],
                new StringValue('ello ello'),
            ];

        yield 'Strings split: simple' =>
            [
                'split("a-b-c", "-")',
                [],
                new ListValue([new StringValue('a'), new StringValue('b'), new StringValue('c')]),
            ];
        yield 'Strings split: one limit' => ['split("a-b-c", "-", 1)', [], new ListValue([new StringValue('a-b-c')])];
        yield 'Strings split: two limit' =>
            [
                'split("a-b-c", "-", 2)',
                [],
                new ListValue([new StringValue('a'), new StringValue('b-c')]),
            ];
        yield 'Strings split: empty delimiter' =>
            [
                'split("hello", "")',
                [],
                new ListValue([
                    new StringValue('h'),
                    new StringValue('e'),
                    new StringValue('l'),
                    new StringValue('l'),
                    new StringValue('o'),
                ]),
            ];

        yield 'Strings toAsciiLower: mixed case' => ['toAsciiLower("TacoCat")', [], new StringValue('tacocat')];
        yield 'Strings toAsciiLower: with non-ascii' =>
            [
                'toAsciiLower("TacoCÆt Xii")',
                [],
                new StringValue('tacocÆt xii'),
            ];
        yield 'Strings toAsciiUpper: mixed case' => ['toAsciiUpper("TacoCat")', [], new StringValue('TACOCAT')];
        yield 'Strings toAsciiUpper: with non-ascii' =>
            [
                'toAsciiUpper("TacoCÆt Xii")',
                [],
                new StringValue('TACOCÆT XII'),
            ];

        // Lists Extension
        yield 'Lists chunk: basic' =>
            [
                'chunk([1, 2, 3, 4, 5], 2)',
                [],
                new ListValue([
                    new ListValue([new IntegerValue(1), new IntegerValue(2)]),
                    new ListValue([new IntegerValue(3), new IntegerValue(4)]),
                    new ListValue([new IntegerValue(5)]),
                ]),
            ];
        yield 'Lists chunk: uneven' =>
            [
                'chunk([1, 2, 3, 4, 5], 3)',
                [],
                new ListValue([
                    new ListValue([new IntegerValue(1), new IntegerValue(2), new IntegerValue(3)]),
                    new ListValue([new IntegerValue(4), new IntegerValue(5)]),
                ]),
            ];
        yield 'Lists chunk: empty list' => ['chunk([], 2)', [], new ListValue([])];
        yield 'Lists chunk: size 1' =>
            [
                'chunk([1, 2, 3], 1)',
                [],
                new ListValue([
                    new ListValue([new IntegerValue(1)]),
                    new ListValue([new IntegerValue(2)]),
                    new ListValue([new IntegerValue(3)]),
                ]),
            ];
        yield 'Lists chunk: zero size error' =>
            [
                'chunk([1, 2, 3], 0)',
                [],
                new RuntimeException('Chunk size must be a positive integer', new Span(0, 15)),
            ];
        yield 'Lists chunk: negative size error' =>
            [
                'chunk([1, 2, 3], -1)',
                [],
                new RuntimeException('Chunk size must be a positive integer', new Span(0, 16)),
            ];

        yield 'Lists contains: found' => ['contains([1, 2, 3], 2)', [], new BooleanValue(true)];
        yield 'Lists contains: not found' => ['contains([1, 2, 3], 4)', [], new BooleanValue(false)];
        yield 'Lists contains: empty list' => ['contains([], 1)', [], new BooleanValue(false)];
        yield 'Lists contains: mixed types found' => ['contains([1, "a", true], "a")', [], new BooleanValue(true)];
        yield 'Lists contains: mixed types not found' => ['contains([1, 2, 3], "2")', [], new BooleanValue(false)];

        yield 'Lists sort: integers' =>
            [
                'sort([3, 1, 2])',
                [],
                new ListValue([new IntegerValue(1), new IntegerValue(2), new IntegerValue(3)]),
            ];
        yield 'Lists sort: strings' =>
            [
                'sort(["c", "a", "b"])',
                [],
                new ListValue([new StringValue('a'), new StringValue('b'), new StringValue('c')]),
            ];
        yield 'Lists sort: empty list' => ['sort([])', [], new ListValue([])];
        yield 'Lists sort: already sorted' =>
            [
                'sort([1, 2, 3])',
                [],
                new ListValue([new IntegerValue(1), new IntegerValue(2), new IntegerValue(3)]),
            ];
        yield 'Lists sort: mixed types error' =>
            [
                'sort([1, "a"])',
                [],
                new RuntimeException('Cannot compare values of type `int` and `string`', new Span(0, 10)),
            ];

        yield 'Lists reverse: integers' =>
            [
                'reverse([1, 2, 3])',
                [],
                new ListValue([new IntegerValue(3), new IntegerValue(2), new IntegerValue(1)]),
            ];
        yield 'Lists reverse: strings' =>
            [
                'reverse(["a", "b", "c"])',
                [],
                new ListValue([new StringValue('c'), new StringValue('b'), new StringValue('a')]),
            ];
        yield 'Lists reverse: empty list' => ['reverse([])', [], new ListValue([])];
        yield 'Lists reverse: single element' => ['reverse([1])', [], new ListValue([new IntegerValue(1)])];

        yield 'Lists join: simple' => ['join(["a", "b", "c"])', [], new StringValue('abc')];
        yield 'Lists join: with separator' => ['join(["a", "b", "c"], "-")', [], new StringValue('a-b-c')];
        yield 'Lists join: empty list' => ['join([], "-")', [], new StringValue('')];
        yield 'Lists join: non-string list error' =>
            [
                'join([1, 2, 3])',
                [],
                new RuntimeException('join: expects a list of strings', new Span(0, 17)),
            ];
    }
}
