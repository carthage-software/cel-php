<?php

declare(strict_types=1);

namespace Cel\Interpreter\Macro;

use Cel\Exception\InvalidMacroCallException;
use Cel\Syntax\Member\CallExpression;
use Cel\Value\OptionalValue;
use Cel\Value\Value;
use Override;
use Psl\Str;

use function assert;

/**
 * Implements the `optional(T).or(optional(T)) -> optional(T)` macro.
 *
 * Returns the target optional when it holds a value, otherwise the alternative
 * optional. The alternative is evaluated lazily: it is only evaluated when the
 * target is empty, allowing chains such as `a.or(b).or(c)` to short-circuit.
 *
 * @example optional.none().or(optional.of(1)) // optional.of(1)
 */
final readonly class OrMacro implements MacroInterface
{
    #[Override]
    public function getName(): string
    {
        return 'or';
    }

    #[Override]
    public function canHandle(CallExpression $call): bool
    {
        return null !== $call->target && 1 === $call->arguments->count();
    }

    #[Override]
    public function execute(CallExpression $call, MacroContextInterface $context): Value
    {
        $target = $call->target;
        assert(null !== $target, 'or() macro requires a target');

        $optional = $context->evaluate($target);
        if (!$optional instanceof OptionalValue) {
            throw new InvalidMacroCallException(
                Str\format('The `or` macro requires an optional target, got `%s`', $optional->getType()),
                $target->getSpan(),
            );
        }

        if ($optional->hasValue()) {
            return $optional;
        }

        $alternativeExpression = $call->arguments->elements[0];
        $alternative = $context->evaluate($alternativeExpression);
        if (!$alternative instanceof OptionalValue) {
            throw new InvalidMacroCallException(
                Str\format('The `or` macro requires an optional argument, got `%s`', $alternative->getType()),
                $alternativeExpression->getSpan(),
            );
        }

        return $alternative;
    }
}
