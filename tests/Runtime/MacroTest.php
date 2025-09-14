<?php

declare(strict_types=1);

namespace Cel\Tests\Runtime;

use Cel\Runtime\Exception\InvalidMacroCallException;
use Cel\Runtime\Exception\NoSuchFunctionException;
use Cel\Runtime\Exception\NoSuchVariableException;
use Cel\Runtime\Exception\RuntimeException;
use Cel\Runtime\Value\BooleanValue;
use Cel\Runtime\Value\IntegerValue;
use Cel\Runtime\Value\ListValue;
use Cel\Runtime\Value\Value;
use Cel\Span\Span;
use Override;

/**
 * @mago-expect lint:halstead
 */
final class MacroTest extends RuntimeTestCase
{
    /**
     * @return iterable<string, array{0: string, 1: array<string, mixed>, 2: Value|RuntimeException}>
     */
    #[Override]
    public static function provideEvaluationCases(): iterable
    {
        // has() macro tests
        yield 'Macro has: message field exists' =>
            ['has(user.name)', ['user' => ['name' => 'Alice']], new BooleanValue(true)];
        yield 'Macro has: message field does not exist' =>
            ['has(user.age)', ['user' => ['name' => 'Alice']], new BooleanValue(false)];
        yield 'Macro has: map key exists' =>
            ['has(params.query)', ['params' => Value::from(['query' => 'cel'])], new BooleanValue(true)];
        yield 'Macro has: map key does not exist' =>
            ['has(params.limit)', ['params' => Value::from(['query' => 'cel'])], new BooleanValue(false)];
        yield 'Macro has: map key exists with null value' =>
            ['has(params.optional)', ['params' => Value::from(['optional' => null])], new BooleanValue(true)];
        yield 'Macro has error: not a member access expression' =>
            [
                'has(1)',
                [],
                new InvalidMacroCallException(
                    'The `has` macro requires a single member access expression as an argument.',
                    new Span(4, 5),
                ),
            ];
        yield 'Macro has error: operand is not a map or message' =>
            [
                'has(items.price)',
                ['items' => [1, 2, 3]],
                new InvalidMacroCallException(
                    'The `has` macro requires a message or map operand, got `list`',
                    new Span(4, 9),
                ),
            ];
        yield 'Macro has fall-through: too many arguments' =>
            [
                'has(user.name, 1)',
                [],
                new NoSuchVariableException('Variable `user` is not defined in the environment', new Span(4, 8)),
            ];
        yield 'Macro has fall-through: no arguments' =>
            [
                'has()',
                [],
                new NoSuchFunctionException('Function `has` is not defined', new Span(0, 3)),
            ];
        yield 'Macro has fall-through: wrong call style (target set)' =>
            [
                'user.has(name)',
                ['user' => ['name' => 'Alice']],
                new NoSuchVariableException('Variable `name` is not defined in the environment', new Span(9, 13)),
            ];

        // all() macro tests
        yield 'Macro all (list): all true' => ['[1, 2, 3].all(x, x > 0)', [], new BooleanValue(true)];
        yield 'Macro all (list): one false causes short-circuit' =>
            ['[1, -1, 3].all(x, x > 0)', [], new BooleanValue(false)];
        yield 'Macro all (list): empty list is true' => ['[].all(x, x > 0)', [], new BooleanValue(true)];
        yield 'Macro all (map): all keys pass' => ['{"a":1, "b":2}.all(k, k.size() == 1)', [], new BooleanValue(true)];
        yield 'Macro all (map): one key fails' =>
            ['{"a":1, "bb":2}.all(k, k.size() == 1)', [], new BooleanValue(false)];
        yield 'Macro all (map): empty map is true' => ['{}.all(k, k.size() == 1)', [], new BooleanValue(true)];
        yield 'Macro all error: predicate returns non-boolean' =>
            [
                '[1, 2, 3].all(x, x + 1)',
                [],
                new InvalidMacroCallException(
                    'The `all` macro predicate must result in a boolean, got `int`',
                    new Span(18, 23),
                ),
            ];
        yield 'Macro all error: target is not list or map' =>
            [
                '1.all(x, x > 0)',
                [],
                new InvalidMacroCallException(
                    'The `all` macro requires a list or map target, got `int`',
                    new Span(0, 1),
                ),
            ];
        yield 'Macro all fall-through: wrong number of arguments (1)' =>
            [
                '[1].all(x)',
                [
                    'x' => 1,
                ],
                new NoSuchFunctionException('Function `all` is not defined', new Span(4, 7)),
            ];
        yield 'Macro all fall-through: wrong call style (no target)' =>
            [
                'all([1], x, x > 0)',
                [
                    'x' => 1,
                ],
                new NoSuchFunctionException('Function `all` is not defined', new Span(0, 3)),
            ];

        // exists() macro tests
        yield 'Macro exists (list): one true causes short-circuit' =>
            ['[1, -1, 3].exists(x, x < 0)', [], new BooleanValue(true)];
        yield 'Macro exists (list): all false' => ['[1, 2, 3].exists(x, x < 0)', [], new BooleanValue(false)];
        yield 'Macro exists (list): empty list is false' => ['[].exists(x, x > 0)', [], new BooleanValue(false)];
        yield 'Macro exists (map): one key matches' =>
            ['{"a":1, "bb":2}.exists(k, k.size() > 1)', [], new BooleanValue(true)];
        yield 'Macro exists (map): no key matches' =>
            ['{"a":1, "b":2}.exists(k, k.size() > 1)', [], new BooleanValue(false)];
        yield 'Macro exists (map): empty map is false' => ['{}.exists(k, k.size() > 1)', [], new BooleanValue(false)];
        yield 'Macro exists error: predicate returns non-boolean' =>
            [
                '[1, 2, 3].exists(x, x + 1)',
                [],
                new InvalidMacroCallException(
                    'The `exists` macro predicate must result in a boolean, got `int`',
                    new Span(19, 24),
                ),
            ];
        yield 'Macro exists error: target is not list or map' =>
            [
                '1.exists(x, x > 0)',
                [],
                new InvalidMacroCallException(
                    'The `exists` macro requires a list or map target, got `int`',
                    new Span(0, 1),
                ),
            ];

        // exists_one() macro tests
        yield 'Macro exists_one (list): exactly one true' =>
            ['[-1, 1, 2].exists_one(x, x < 0)', [], new BooleanValue(true)];
        yield 'Macro exists_one (list): zero trues' => ['[1, 2, 3].exists_one(x, x < 0)', [], new BooleanValue(false)];
        yield 'Macro exists_one (list): multiple trues' =>
            ['[-1, -2, 1].exists_one(x, x < 0)', [], new BooleanValue(false)];
        yield 'Macro exists_one (list): empty list is false' =>
            ['[].exists_one(x, x > 0)', [], new BooleanValue(false)];
        yield 'Macro exists_one (map): exactly one matching key' =>
            ['{"a":1, "bb":2}.exists_one(k, k.size() > 1)', [], new BooleanValue(true)];
        yield 'Macro exists_one (map): multiple matching keys' =>
            ['{"aa":1, "bb":2}.exists_one(k, k.size() > 1)', [], new BooleanValue(false)];
        yield 'Macro exists_one (map): no matching keys' =>
            ['{"a":1, "b":2}.exists_one(k, k.size() > 1)', [], new BooleanValue(false)];
        yield 'Macro exists_one error: predicate returns non-boolean' =>
            [
                '[1, 2, 3].exists_one(x, x + 1)',
                [],
                new InvalidMacroCallException(
                    'The `exists_one` macro predicate must result in a boolean, got `int`',
                    new Span(22, 27),
                ),
            ];

        // map() macro tests
        yield 'Macro map (list): simple transform' =>
            [
                '[1, 2, 3].map(i, i * 2)',
                [],
                new ListValue([new IntegerValue(2), new IntegerValue(4), new IntegerValue(6)]),
            ];
        yield 'Macro map (list): with filter, some pass' =>
            [
                '[1, 2, 3, 4].map(i, i % 2 == 0, i * 10)',
                [],
                new ListValue([new IntegerValue(20), new IntegerValue(40)]),
            ];
        yield 'Macro map (list): with filter, none pass' =>
            ['[1, 3, 5].map(i, i % 2 == 0, i * 10)', [], new ListValue([])];
        yield 'Macro map (map): simple transform on keys' =>
            ['{"a": 1, "b": 2}.map(k, k + k)', [], Value::from(['aa', 'bb'])];
        yield 'Macro map (map): with filter on keys' =>
            [
                '{"apple": 1, "banana": 2, "avocado": 3}.map(k, k.startsWith("a"), k.size())',
                [],
                new ListValue([new IntegerValue(5), new IntegerValue(7)]),
            ];
        yield 'Macro map error: target is not list or map' =>
            [
                '"hello".map(c, c)',
                [],
                new InvalidMacroCallException(
                    'The `map` macro requires a list or map target, got `string`',
                    new Span(0, 7),
                ),
            ];
        yield 'Macro map error: filter is not boolean' =>
            [
                '[1, 2, 3].map(i, i, i)',
                [],
                new InvalidMacroCallException(
                    'The `map` macro filter must result in a boolean, got `int`',
                    new Span(18, 19),
                ),
            ];
        yield 'Macro map fall-through: wrong number of arguments (1)' =>
            [
                '[1].map(x)',
                ['x' => false],
                new NoSuchFunctionException('Function `map` is not defined', new Span(4, 7)),
            ];
        yield 'Macro map fall-through: wrong number of arguments (4)' =>
            [
                '[1].map(x, true, x, x)',
                ['x' => false],
                new NoSuchFunctionException('Function `map` is not defined', new Span(4, 7)),
            ];

        // filter() macro tests
        yield 'Macro filter (list): simple filter' =>
            ['[1, 2, 3, 4].filter(i, i % 2 == 0)', [], new ListValue([new IntegerValue(2), new IntegerValue(4)])];
        yield 'Macro filter (list): empty result' => ['[1, 3, 5].filter(i, i % 2 == 0)', [], new ListValue([])];
        yield 'Macro filter (map): filter on keys' =>
            ['{"apple": 1, "banana": 2, "avocado": 3}.filter(k, k.size() > 5)', [], Value::from(['banana', 'avocado'])];
        yield 'Macro filter (map): empty result' => ['{"a": 1, "b": 2}.filter(k, k == "c")', [], new ListValue([])];
        yield 'Macro filter (list): empty list' => ['[].filter(i, true)', [], new ListValue([])];
        yield 'Macro filter (map): empty map' => ['{}.filter(k, true)', [], new ListValue([])];
        yield 'Macro filter error: predicate is not boolean' =>
            [
                '[1, 2, 3].filter(i, i * 10)',
                [],
                new InvalidMacroCallException(
                    'The `filter` macro predicate must result in a boolean, got `int`',
                    new Span(21, 27),
                ),
            ];
        yield 'Macro filter fall-through: wrong number of arguments (1)' =>
            [
                '[1].filter(x)',
                ['x' => true],
                new NoSuchFunctionException('Function `filter` is not defined', new Span(4, 10)),
            ];
        yield 'Macro filter fall-through: wrong number of arguments (3)' =>
            [
                '[1].filter(x, true, false)',
                ['x' => true],
                new NoSuchFunctionException('Function `filter` is not defined', new Span(4, 10)),
            ];
    }
}
