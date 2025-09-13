<?php

declare(strict_types=1);

namespace Cel\Tests\Runtime;

use Cel\Runtime\Exception\InvalidMacroCallException;
use Cel\Runtime\Exception\RuntimeException;
use Cel\Runtime\Interpreter\TreeWalking\TreeWalkingInterpreter;
use Cel\Runtime\Runtime;
use Cel\Runtime\Value\BooleanValue;
use Cel\Runtime\Value\Value;
use Cel\Span\Span;
use Override;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\Attributes\Medium;
use Psl\DateTime;

#[CoversClass(Runtime::class)]
#[CoversClass(TreeWalkingInterpreter::class)]
#[Medium]
final class MacroTest extends RuntimeTestCase
{
    /**
     * @return iterable<string, array{0: string, 1: array<string, mixed>, 2: Value|RuntimeException}>
     */
    #[Override]
    public static function provideEvaluationCases(): iterable
    {
        yield 'Macro has: message field exists' =>
            [
                'has(user.name)',
                ['user' => ['name' => 'Alice']],
                new BooleanValue(true),
            ];
        yield 'Macro has: message field does not exist' =>
            [
                'has(user.age)',
                ['user' => ['name' => 'Alice']],
                new BooleanValue(false),
            ];
        yield 'Macro has: map key exists' =>
            [
                'has(params.query)',
                ['params' => Value::from(['query' => 'cel'])],
                new BooleanValue(true),
            ];
        yield 'Macro has: map key does not exist' =>
            [
                'has(params.limit)',
                ['params' => Value::from(['query' => 'cel'])],
                new BooleanValue(false),
            ];
        yield 'Macro has error: invalid argument type (not member access)' =>
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

        yield 'Macro all: all true' => ['[1, 2, 3].all(x, x > 0)', [], new BooleanValue(true)];
        yield 'Macro all: one false' => ['[1, -1, 3].all(x, x > 0)', [], new BooleanValue(false)];
        yield 'Macro all: empty list' => ['[].all(x, x > 0)', [], new BooleanValue(true)];
        yield 'Macro all: complex predicate' =>
            [
                'requests.all(r, r.amount < 1000 && r.timestamp > now)',
                [
                    'now' => DateTime\Timestamp::monotonic()->getSeconds(),
                    'requests' => [
                        ['amount' => 100, 'timestamp' => DateTime\Timestamp::monotonic()->getSeconds() + 10],
                        ['amount' => 200, 'timestamp' => DateTime\Timestamp::monotonic()->getSeconds() + 20],
                    ],
                ],
                new BooleanValue(true),
            ];
        yield 'Macro all error: predicate returns non-boolean' =>
            [
                '[1, 2, 3].all(x, x + 1)',
                [],
                new InvalidMacroCallException(
                    'The `all` macro predicate must result in a boolean, got `int`',
                    new Span(18, 23),
                ),
            ];
        yield 'Macro all error: target is not a list' =>
            [
                '1.all(x, x > 0)',
                [],
                new InvalidMacroCallException('The `all` macro requires a list target, got `int`', new Span(0, 1)),
            ];
    }
}
