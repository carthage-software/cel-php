<?php

declare(strict_types=1);

namespace Cel\Tests\Runtime;

use Cel\Exception\EvaluationException;
use Cel\Exception\NoSuchVariableException;
use Cel\Span\Span;
use Cel\Value\BooleanValue;
use Cel\Value\IntegerValue;
use Cel\Value\StringValue;
use Cel\Value\Value;
use Override;

/**
 * Covers CEL's qualified-name resolution: a dotted chain may name a bound
 * variable (longest-prefix wins), and a leading dot forces an absolute lookup
 * against the root namespace, bypassing comprehension-local bindings.
 */
final class QualifiedNameResolutionTest extends RuntimeTestCase
{
    /**
     * @return iterable<string, array{0: string, 1: array<string, mixed>, 2: Value|EvaluationException}>
     */
    #[Override]
    public static function provideEvaluationCases(): iterable
    {
        yield 'qualified variable name' => ['x.y', ['x.y' => true], new BooleanValue(true)];
        yield 'deeply qualified variable name' => ['a.b.c', ['a.b.c' => 42], new IntegerValue(42)];
        yield 'longest qualified prefix wins' => [
            'a.b.c',
            ['a.b' => ['c' => 7], 'a.b.c' => 9],
            new IntegerValue(9),
        ];
        yield 'bound root prefers field selection over qualified name' => [
            'x.y',
            ['x' => ['y' => 1], 'x.y' => 99],
            new IntegerValue(1),
        ];
        yield 'unresolved qualified name errors on its root' => [
            'x.y',
            [],
            new NoSuchVariableException('Variable `x` is not defined in the environment', new Span(0, 0)),
        ];

        yield 'absolute reference resolves at the root' => ['.x', ['x' => 'root'], new StringValue('root')];
        yield 'absolute qualified reference resolves at the root' => [
            '.x.y',
            ['x.y' => 'deep'],
            new StringValue('deep'),
        ];
        yield 'absolute reference bypasses comprehension shadowing' => [
            "['inner'].exists(x, .x == 'outer')",
            ['x' => 'outer'],
            new BooleanValue(true),
        ];
        yield 'absolute qualified reference bypasses comprehension shadowing' => [
            "['inner'].exists(x, .x.y == 'zed')",
            ['x.y' => 'zed'],
            new BooleanValue(true),
        ];
        yield 'comprehension variable still shadows for relative references' => [
            "['inner'].exists(x, x == 'inner')",
            ['x' => 'outer'],
            new BooleanValue(true),
        ];
        yield 'unresolved absolute reference errors' => [
            '.missing',
            [],
            new NoSuchVariableException('Variable `missing` is not defined in the environment', new Span(0, 0)),
        ];
    }
}
