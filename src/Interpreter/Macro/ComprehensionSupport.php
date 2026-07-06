<?php

declare(strict_types=1);

namespace Cel\Interpreter\Macro;

use Cel\Exception\EvaluationException;
use Cel\Exception\InvalidMacroCallException;
use Cel\Syntax\Expression;
use Cel\Syntax\Member\CallExpression;
use Cel\Syntax\Member\IdentifierExpression;
use Cel\Value\IntegerValue;
use Cel\Value\ListValue;
use Cel\Value\MapValue;
use Cel\Value\Value;
use Psl\Str;

use function assert;

/**
 * Shared iteration support for the comprehension macros (`all`, `exists`,
 * `existsOne`, `transformList`, `transformMap`).
 *
 * A single-variable comprehension binds the element (for a list) or the key
 * (for a map). A two-variable comprehension binds the index/key as the first
 * variable and the value as the second.
 */
trait ComprehensionSupport
{
    /**
     * Evaluates the macro target and produces the variable bindings for each
     * iteration.
     *
     * @param non-empty-string $macro
     * @param int $variableCount 1 for a single-variable macro, 2 for a two-variable one.
     *
     * @return list<array<string, Value>>
     *
     * @throws InvalidMacroCallException If the variables or target are invalid.
     * @throws EvaluationException If evaluating the target fails.
     */
    private static function comprehensionBindings(
        string $macro,
        CallExpression $call,
        MacroContextInterface $context,
        int $variableCount,
    ): array {
        $target = $call->target;
        assert(null !== $target, 'a comprehension macro requires a target');

        $first = self::iterationVariable($macro, $call->arguments->elements[0]);
        $second = 2 === $variableCount ? self::iterationVariable($macro, $call->arguments->elements[1]) : null;

        $value = $context->evaluate($target);
        if (!$value instanceof ListValue && !$value instanceof MapValue) {
            throw new InvalidMacroCallException(
                Str\format('The `%s` macro requires a list or map target, got `%s`', $macro, $value->getType()),
                $target->getSpan(),
            );
        }

        $bindings = [];
        if ($value instanceof ListValue) {
            foreach ($value->value as $index => $element) {
                $bindings[] = null === $second
                    ? [$first => $element]
                    : [$first => new IntegerValue($index), $second => $element];
            }

            return $bindings;
        }

        foreach ($value->value as $key => $entryValue) {
            $keyValue = Value::from($key);
            $bindings[] = null === $second ? [$first => $keyValue] : [$first => $keyValue, $second => $entryValue];
        }

        return $bindings;
    }

    /**
     * @param non-empty-string $macro
     *
     * @throws InvalidMacroCallException If the argument is not an identifier.
     */
    private static function iterationVariable(string $macro, Expression $argument): string
    {
        if (!$argument instanceof IdentifierExpression) {
            throw new InvalidMacroCallException(
                Str\format('The `%s` macro requires its iteration variables to be identifiers.', $macro),
                $argument->getSpan(),
            );
        }

        return $argument->identifier->name;
    }
}
