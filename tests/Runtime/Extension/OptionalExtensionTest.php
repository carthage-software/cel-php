<?php

declare(strict_types=1);

namespace Cel\Tests\Runtime\Extension;

use Cel\Exception\EvaluationException;
use Cel\Exception\InvalidMacroCallException;
use Cel\Exception\InvalidOptionalConstructionException;
use Cel\Exception\NoSuchFunctionException;
use Cel\Exception\NoSuchOverloadException;
use Cel\Exception\NoSuchVariableException;
use Cel\Exception\OptionalDereferenceException;
use Cel\Runtime\Configuration;
use Cel\Span\Span;
use Cel\Tests\Fixture\ProfileMessage;
use Cel\Tests\Runtime\RuntimeTestCase;
use Cel\Value\BooleanValue;
use Cel\Value\IntegerValue;
use Cel\Value\MessageValue;
use Cel\Value\StringValue;
use Cel\Value\TypeValue;
use Cel\Value\Value;
use Override;

final class OptionalExtensionTest extends RuntimeTestCase
{
    /**
     * @return iterable<string, array{
     *     0: string,
     *     1: array<string, mixed>,
     *     2: Value|EvaluationException,
     *     3?: null|Configuration
     * }>
     */
    #[Override]
    public static function provideEvaluationCases(): iterable
    {
        yield from self::provideConstructorAndAccessorCases();
        yield from self::provideSelectionCases();
        yield from self::provideIndexingCases();
        yield from self::provideMacroCases();
        yield from self::provideEqualityCases();
        yield from self::provideConstructionCases();
        yield from self::provideNamespaceCases();
        yield from self::provideListAndZeroValueCases();
    }

    /**
     * @return iterable<string, array{0: string, 1: array<string, mixed>, 2: Value|EvaluationException}>
     */
    private static function provideListAndZeroValueCases(): iterable
    {
        // first / last
        yield 'first present' => ['[10, 20, 30].first().value()', [], new IntegerValue(10)];
        yield 'first empty' => ['[].first().hasValue()', [], new BooleanValue(false)];
        yield 'last present' => ['[10, 20, 30].last().value()', [], new IntegerValue(30)];
        yield 'last empty' => ['[].last().hasValue()', [], new BooleanValue(false)];

        // unwrap / unwrapOpt
        yield 'unwrap keeps present optionals' => [
            'optional.unwrap([optional.of(1), optional.none(), optional.of(3)])',
            [],
            Value::from([1, 3]),
        ];
        yield 'unwrap all empty yields empty list' => [
            'optional.unwrap([optional.none(), optional.none()])',
            [],
            Value::from([]),
        ];
        yield 'unwrapOpt postfix form' => [
            '[optional.of(1), optional.none()].unwrapOpt()',
            [],
            Value::from([1]),
        ];
        yield 'unwrap requires optional elements' => [
            'optional.unwrap([1, 2])',
            [],
            new EvaluationException('unwrap requires a list of optionals, got `int` element', new Span(0, 0)),
        ];
        yield 'unwrapOpt requires optional elements' => [
            '[1].unwrapOpt()',
            [],
            new EvaluationException('unwrap requires a list of optionals, got `int` element', new Span(0, 0)),
        ];

        // ofNonZeroValue across additional types
        yield 'ofNonZeroValue zero duration' => [
            'optional.ofNonZeroValue(duration("0s")).hasValue()',
            [],
            new BooleanValue(false),
        ];
        yield 'ofNonZeroValue non-zero duration' => [
            'optional.ofNonZeroValue(duration("1h")).hasValue()',
            [],
            new BooleanValue(true),
        ];
        yield 'ofNonZeroValue epoch timestamp is present' => [
            'optional.ofNonZeroValue(timestamp("1970-01-01T00:00:00Z")).hasValue()',
            [],
            new BooleanValue(true),
        ];
        yield 'ofNonZeroValue empty bytes' => [
            'optional.ofNonZeroValue(b"").hasValue()',
            [],
            new BooleanValue(false),
        ];
        yield 'ofNonZeroValue false' => ['optional.ofNonZeroValue(false).hasValue()', [], new BooleanValue(false)];
        yield 'ofNonZeroValue zero uint' => ['optional.ofNonZeroValue(0u).hasValue()', [], new BooleanValue(false)];
        yield 'ofNonZeroValue zero float' => ['optional.ofNonZeroValue(0.0).hasValue()', [], new BooleanValue(false)];
        yield 'ofNonZeroValue empty map' => ['optional.ofNonZeroValue({}).hasValue()', [], new BooleanValue(false)];
        yield 'ofNonZeroValue nested optional is present' => [
            'optional.ofNonZeroValue(optional.none()).hasValue()',
            [],
            new BooleanValue(true),
        ];
    }

    /**
     * @return iterable<string, array{0: string, 1: array<string, mixed>, 2: Value|EvaluationException}>
     */
    private static function provideConstructorAndAccessorCases(): iterable
    {
        yield 'of value' => ['optional.of(1).value()', [], new IntegerValue(1)];
        yield 'of hasValue' => ['optional.of(1).hasValue()', [], new BooleanValue(true)];
        yield 'none hasValue' => ['optional.none().hasValue()', [], new BooleanValue(false)];
        yield 'none value dereference error' => [
            'optional.none().value()',
            [],
            new OptionalDereferenceException('optional.none() dereference', new Span(0, 0)),
        ];

        yield 'ofNonZeroValue zero int' => ['optional.ofNonZeroValue(0).hasValue()', [], new BooleanValue(false)];
        yield 'ofNonZeroValue nonzero int' => ['optional.ofNonZeroValue(7).value()', [], new IntegerValue(7)];
        yield 'ofNonZeroValue empty string' => ['optional.ofNonZeroValue("").hasValue()', [], new BooleanValue(false)];
        yield 'ofNonZeroValue null' => ['optional.ofNonZeroValue(null).hasValue()', [], new BooleanValue(false)];
        yield 'ofNonZeroValue empty list' => ['optional.ofNonZeroValue([]).hasValue()', [], new BooleanValue(false)];
        yield 'ofNonZeroValue nonempty string' => [
            'optional.ofNonZeroValue("cel").value()',
            [],
            new StringValue('cel'),
        ];

        yield 'type of optional' => ['type(optional.of(1))', [], new TypeValue('optional_type')];
    }

    /**
     * @return iterable<string, array{0: string, 1: array<string, mixed>, 2: Value|EvaluationException, 3?: null|Configuration}>
     */
    private static function provideSelectionCases(): iterable
    {
        $messageConfig = Configuration::forAllowedMessages([ProfileMessage::class]);

        // Optional field selection on maps.
        yield 'map optional select present' => ['{"a": 1}.?a.value()', [], new IntegerValue(1)];
        yield 'map optional select absent' => ['{"a": 1}.?b.hasValue()', [], new BooleanValue(false)];
        yield 'optional select on unselectable type' => [
            '"str".?a.hasValue()',
            [],
            new NoSuchOverloadException('Cannot access member `a` on type `string`', new Span(0, 0)),
        ];

        // Optional field selection on messages.
        yield 'message optional select present' => [
            'cel.tests.fixture.ProfileMessage{name: "x"}.?name.value()',
            [],
            new StringValue('x'),
            $messageConfig,
        ];
        yield 'message optional select absent' => [
            'cel.tests.fixture.ProfileMessage{name: "x"}.?nickname.hasValue()',
            [],
            new BooleanValue(false),
            $messageConfig,
        ];

        // Viral propagation: a regular selection after an optional is treated as optional.
        yield 'viral select through present optional' => [
            'optional.of({"a": {"b": 2}}).a.b.value()',
            [],
            new IntegerValue(2),
        ];
        yield 'viral select absent through present optional' => [
            'optional.of({"a": 1}).b.hasValue()',
            [],
            new BooleanValue(false),
        ];
        yield 'viral select through empty optional' => ['optional.none().a.hasValue()', [], new BooleanValue(false)];
        yield 'viral chaining dot-dot equals dot-optional' => [
            '{"a": {"b": 2}}.?a.b.value()',
            [],
            new IntegerValue(2),
        ];
        yield 'viral chaining dot-optional-optional' => ['{"a": {"b": 2}}.?a.?b.value()', [], new IntegerValue(2)];
        yield 'viral select on unselectable inner' => [
            'optional.of(1).a.hasValue()',
            [],
            new NoSuchOverloadException('Cannot access member `a` on type `int`', new Span(0, 0)),
        ];
    }

    /**
     * @return iterable<string, array{0: string, 1: array<string, mixed>, 2: Value|EvaluationException, 3?: null|Configuration}>
     */
    private static function provideIndexingCases(): iterable
    {
        $messageConfig = Configuration::forAllowedMessages([ProfileMessage::class]);

        // Optional list indexing.
        yield 'list optional index present' => ['[10, 20][?1].value()', [], new IntegerValue(20)];
        yield 'list optional index out of bounds' => ['[10][?5].hasValue()', [], new BooleanValue(false)];
        yield 'list optional index negative' => ['[10][?-1].hasValue()', [], new BooleanValue(false)];
        yield 'list optional index wrong type' => [
            '[10][?"x"].hasValue()',
            [],
            new NoSuchOverloadException('List indices must be integer, got `string`', new Span(0, 0)),
        ];

        // Optional map indexing.
        yield 'map optional index present' => ['{"a": 1}[?"a"].value()', [], new IntegerValue(1)];
        yield 'map optional index absent' => ['{"a": 1}[?"b"].hasValue()', [], new BooleanValue(false)];
        yield 'map optional index integer key' => ['{1: 2}[?1].value()', [], new IntegerValue(2)];
        yield 'map optional index wrong key type' => [
            '{"a": 1}[?[1]].hasValue()',
            [],
            new NoSuchOverloadException(
                'Map keys must be string, integer, unsigned integer, or double, got `list`',
                new Span(0, 0),
            ),
        ];

        // Optional message indexing.
        yield 'message optional index present' => [
            'cel.tests.fixture.ProfileMessage{name: "x"}[?"name"].value()',
            [],
            new StringValue('x'),
            $messageConfig,
        ];
        yield 'message optional index absent' => [
            'cel.tests.fixture.ProfileMessage{name: "x"}[?"nickname"].hasValue()',
            [],
            new BooleanValue(false),
            $messageConfig,
        ];
        yield 'message optional index wrong key type' => [
            'cel.tests.fixture.ProfileMessage{name: "x"}[?1].hasValue()',
            [],
            new NoSuchOverloadException('Message fields must be accessed by string, got `int`', new Span(0, 0)),
            $messageConfig,
        ];

        // Optional indexing on an unindexable type.
        yield 'optional index on unindexable type' => [
            '"str"[?0].hasValue()',
            [],
            new NoSuchOverloadException(
                'Indexing is only supported on lists, maps, and messages, got `string`',
                new Span(0, 0),
            ),
        ];

        // Viral propagation for indexing.
        yield 'viral index through present optional' => ['optional.of([10, 20])[0].value()', [], new IntegerValue(10)];
        yield 'viral index through empty optional' => ['optional.none()[0].hasValue()', [], new BooleanValue(false)];
        yield 'viral optional index out of bounds through optional' => [
            'optional.of([10])[?9].hasValue()',
            [],
            new BooleanValue(false),
        ];
    }

    /**
     * @return iterable<string, array{0: string, 1: array<string, mixed>, 2: Value|EvaluationException}>
     */
    private static function provideMacroCases(): iterable
    {
        // or
        yield 'or first present' => ['optional.of(1).or(optional.of(2)).value()', [], new IntegerValue(1)];
        yield 'or first empty' => ['optional.none().or(optional.of(2)).value()', [], new IntegerValue(2)];
        yield 'or both empty' => ['optional.none().or(optional.none()).hasValue()', [], new BooleanValue(false)];
        yield 'or is lazy when first is present' => [
            'optional.of(1).or(optional.of(1 / 0)).value()',
            [],
            new IntegerValue(1),
        ];
        yield 'or requires optional target' => [
            '(1).or(optional.of(2))',
            [],
            new InvalidMacroCallException('The `or` macro requires an optional target, got `int`', new Span(0, 0)),
        ];
        yield 'or requires optional argument' => [
            'optional.none().or(1)',
            [],
            new InvalidMacroCallException('The `or` macro requires an optional argument, got `int`', new Span(0, 0)),
        ];

        // orValue
        yield 'orValue present' => ['optional.of(5).orValue(9)', [], new IntegerValue(5)];
        yield 'orValue empty' => ['optional.none().orValue(9)', [], new IntegerValue(9)];
        yield 'orValue is lazy when present' => ['optional.of(5).orValue(1 / 0)', [], new IntegerValue(5)];
        yield 'orValue requires optional target' => [
            '(1).orValue(2)',
            [],
            new InvalidMacroCallException('The `orValue` macro requires an optional target, got `int`', new Span(0, 0)),
        ];

        // optMap
        yield 'optMap present' => ['optional.of(42).optMap(y, y + 1).value()', [], new IntegerValue(43)];
        yield 'optMap empty' => ['optional.none().optMap(y, y + 1).hasValue()', [], new BooleanValue(false)];
        yield 'optMap requires identifier' => [
            'optional.of(1).optMap(2, 3).hasValue()',
            [],
            new InvalidMacroCallException(
                'The `optMap` macro requires the first argument to be an identifier.',
                new Span(0, 0),
            ),
        ];
        yield 'optMap requires optional target' => [
            '(1).optMap(y, y)',
            [],
            new InvalidMacroCallException('The `optMap` macro requires an optional target, got `int`', new Span(0, 0)),
        ];

        // optFlatMap
        yield 'optFlatMap present' => ['{"k": {"n": "v"}}.?k.optFlatMap(m, m.?n).value()', [], new StringValue('v')];
        yield 'optFlatMap empty' => [
            'optional.none().optFlatMap(y, optional.of(y)).hasValue()',
            [],
            new BooleanValue(false),
        ];
        yield 'optFlatMap flattens nested none' => [
            '{"k": {"other": 1}}.?k.optFlatMap(m, m.?n).hasValue()',
            [],
            new BooleanValue(false),
        ];
        yield 'optFlatMap requires optional result' => [
            'optional.of(1).optFlatMap(y, y + 1).hasValue()',
            [],
            new InvalidMacroCallException(
                'The `optFlatMap` macro transform must result in an optional, got `int`',
                new Span(0, 0),
            ),
        ];
        yield 'optFlatMap requires identifier' => [
            'optional.of(1).optFlatMap(2, optional.of(3)).hasValue()',
            [],
            new InvalidMacroCallException(
                'The `optFlatMap` macro requires the first argument to be an identifier.',
                new Span(0, 0),
            ),
        ];
        yield 'optFlatMap requires optional target' => [
            '(1).optFlatMap(y, optional.of(y))',
            [],
            new InvalidMacroCallException(
                'The `optFlatMap` macro requires an optional target, got `int`',
                new Span(0, 0),
            ),
        ];
    }

    /**
     * @return iterable<string, array{0: string, 1: array<string, mixed>, 2: Value|EvaluationException}>
     */
    private static function provideEqualityCases(): iterable
    {
        yield 'equality empty equals empty' => ['optional.none() == optional.none()', [], new BooleanValue(true)];
        yield 'equality present equals present' => ['optional.of(1) == optional.of(1)', [], new BooleanValue(true)];
        yield 'equality present differs from present' => [
            'optional.of(1) == optional.of(2)',
            [],
            new BooleanValue(false),
        ];
        yield 'equality present differs from empty' => [
            'optional.of(1) == optional.none()',
            [],
            new BooleanValue(false),
        ];
        yield 'inequality empty vs present' => ['optional.none() != optional.of(1)', [], new BooleanValue(true)];
        yield 'inequality present vs present equal' => [
            'optional.of(1) != optional.of(1)',
            [],
            new BooleanValue(false),
        ];
    }

    /**
     * @return iterable<string, array{0: string, 1: array<string, mixed>, 2: Value|EvaluationException, 3?: null|Configuration}>
     */
    private static function provideConstructionCases(): iterable
    {
        $messageConfig = Configuration::forAllowedMessages([ProfileMessage::class]);

        // Map construction.
        yield 'map optional entry included' => [
            '{?"x": optional.of(1), "y": 2}',
            [],
            Value::from(['x' => 1, 'y' => 2]),
        ];
        yield 'map optional entry omitted' => ['{?"x": optional.none(), "y": 2}', [], Value::from(['y' => 2])];
        yield 'map optional entry integer key' => ['{?1: optional.of(2)}', [], Value::from([1 => 2])];
        yield 'map optional entry requires optional value' => [
            '{?"x": 5}',
            [],
            new InvalidOptionalConstructionException(
                'Optional map entry requires an optional value, got `int`',
                new Span(0, 0),
            ),
        ];

        // List construction.
        yield 'list optional element included' => ['[1, ?optional.of(2), 3]', [], Value::from([1, 2, 3])];
        yield 'list optional element omitted' => ['[1, ?optional.none(), 3]', [], Value::from([1, 3])];
        yield 'list optional elements mixed' => [
            '[?optional.of(1), 2, ?optional.none()]',
            [],
            Value::from([1, 2]),
        ];
        yield 'list optional element requires optional value' => [
            '[?5]',
            [],
            new InvalidOptionalConstructionException(
                'Optional list element requires an optional value, got `int`',
                new Span(0, 0),
            ),
        ];

        // Message construction.
        yield 'message optional field set' => [
            'cel.tests.fixture.ProfileMessage{name: "a", ?nickname: optional.of("nick")}',
            [],
            new MessageValue(new ProfileMessage('a', 'nick'), [
                'name' => new StringValue('a'),
                'nickname' => new StringValue('nick'),
            ]),
            $messageConfig,
        ];
        yield 'message optional field omitted' => [
            'cel.tests.fixture.ProfileMessage{name: "a", ?nickname: optional.none()}',
            [],
            new MessageValue(new ProfileMessage('a'), ['name' => new StringValue('a')]),
            $messageConfig,
        ];
        yield 'message optional field requires optional value' => [
            'cel.tests.fixture.ProfileMessage{name: "a", ?nickname: "raw"}',
            [],
            new InvalidOptionalConstructionException(
                'Optional field initializer requires an optional value, got `string`',
                new Span(0, 0),
            ),
            $messageConfig,
        ];
    }

    /**
     * @return iterable<string, array{0: string, 1: array<string, mixed>, 2: Value|EvaluationException}>
     */
    private static function provideNamespaceCases(): iterable
    {
        yield 'namespace shadowed by variable' => [
            'optional.of(1)',
            ['optional' => ['x' => 1]],
            new NoSuchFunctionException('Function `of` is not defined', new Span(0, 0)),
        ];
        yield 'unknown namespace falls through to variable lookup' => [
            'unknownns.fn(1)',
            [],
            new NoSuchVariableException('Variable `unknownns` is not defined in the environment', new Span(0, 0)),
        ];
        yield 'namespaced call with wrong arity' => [
            'optional.none(1)',
            [],
            new NoSuchOverloadException(
                'Invalid arguments for function "none". Got `(int)`, but expected one of: `()`',
                new Span(0, 0),
            ),
        ];
    }

    public function testOfIsIdempotent(): void
    {
        static::assertTrue($this->evaluate('optional.of(1)')->idempotent);
    }

    public function testOfNonZeroValueIsIdempotent(): void
    {
        static::assertTrue($this->evaluate('optional.ofNonZeroValue(1)')->idempotent);
    }

    public function testNoneIsIdempotent(): void
    {
        static::assertTrue($this->evaluate('optional.none()')->idempotent);
    }

    public function testValueIsIdempotent(): void
    {
        static::assertTrue($this->evaluate('optional.of(1).value()')->idempotent);
    }

    public function testHasValueIsIdempotent(): void
    {
        static::assertTrue($this->evaluate('optional.of(1).hasValue()')->idempotent);
    }

    public function testFirstIsIdempotent(): void
    {
        static::assertTrue($this->evaluate('[1].first()')->idempotent);
    }

    public function testLastIsIdempotent(): void
    {
        static::assertTrue($this->evaluate('[1].last()')->idempotent);
    }

    public function testUnwrapIsIdempotent(): void
    {
        static::assertTrue($this->evaluate('optional.unwrap([optional.of(1)])')->idempotent);
    }

    public function testUnwrapOptIsIdempotent(): void
    {
        static::assertTrue($this->evaluate('[optional.of(1)].unwrapOpt()')->idempotent);
    }
}
