<?php

declare(strict_types=1);

namespace Cel\Interpreter\Macro;

use Cel\Exception\InvalidMacroCallException;
use Cel\Syntax\Member\CallExpression;
use Cel\Value\OptionalValue;
use Cel\Value\Value;
use Override;

use function assert;
use function sprintf;

/**
 * Implements the `optional(T).orValue(T) -> T` macro.
 *
 * Returns the value contained by the target optional, or the alternative value
 * when the optional is empty. The alternative is evaluated lazily: it is only
 * evaluated when the target is empty.
 *
 * @example {'k': 'v'}[?'missing'].orValue('default') // "default"
 *
 * @internal
 */
final readonly class OrValueMacro implements MacroInterface
{
    #[Override]
    public function getName(): string
    {
        return 'orValue';
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
        assert(null !== $target, 'orValue() macro requires a target');

        $optional = $context->evaluate($target);
        if (!$optional instanceof OptionalValue) {
            throw new InvalidMacroCallException(
                sprintf('The `orValue` macro requires an optional target, got `%s`', $optional->getType()),
                $target->getSpan(),
            );
        }

        $inner = $optional->value;
        if (null !== $inner) {
            return $inner;
        }

        return $context->evaluate($call->arguments->at(0));
    }
}
