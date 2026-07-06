<?php

declare(strict_types=1);

namespace Cel\Interpreter\Macro;

use Cel\Exception\InvalidMacroCallException;
use Cel\Syntax\Member\CallExpression;
use Cel\Syntax\Member\IdentifierExpression;
use Cel\Value\OptionalValue;
use Cel\Value\Value;
use Override;

use function assert;
use function sprintf;

/**
 * Implements the `optional(T).optFlatMap(T var, T -> optional(U)) -> optional(U)` macro.
 *
 * When the target optional holds a value, the value is bound to `var` and the
 * transformation expression is evaluated; the transformation must itself produce
 * an optional, which is returned directly (flattened). When the target is empty,
 * the transformation is not evaluated and `optional.none()` is returned.
 *
 * @example {'k': {'n': 'v'}}.?k.optFlatMap(m, m.?n).value() // "v"
 */
final readonly class OptFlatMapMacro implements MacroInterface
{
    #[Override]
    public function getName(): string
    {
        return 'optFlatMap';
    }

    #[Override]
    public function canHandle(CallExpression $call): bool
    {
        return null !== $call->target && 2 === $call->arguments->count();
    }

    #[Override]
    public function execute(CallExpression $call, MacroContextInterface $context): Value
    {
        $target = $call->target;
        assert(null !== $target, 'optFlatMap() macro requires a target');

        $name = $call->arguments->elements[0];
        if (!$name instanceof IdentifierExpression) {
            throw new InvalidMacroCallException(
                'The `optFlatMap` macro requires the first argument to be an identifier.',
                $name->getSpan(),
            );
        }

        $optional = $context->evaluate($target);
        if (!$optional instanceof OptionalValue) {
            throw new InvalidMacroCallException(
                sprintf('The `optFlatMap` macro requires an optional target, got `%s`', $optional->getType()),
                $target->getSpan(),
            );
        }

        $inner = $optional->value;
        if (null === $inner) {
            return OptionalValue::none();
        }

        $transform = $call->arguments->elements[1];
        $variableName = $name->identifier->name;
        $environment = $context->getEnvironment()->fork();

        /** @var OptionalValue */
        return $context->withEnvironment($environment, static function () use (
            $environment,
            $variableName,
            $inner,
            $transform,
            $context,
        ): OptionalValue {
            $environment->addVariable($variableName, $inner);

            $result = $context->evaluate($transform);
            if (!$result instanceof OptionalValue) {
                throw new InvalidMacroCallException(
                    sprintf(
                        'The `optFlatMap` macro transform must result in an optional, got `%s`',
                        $result->getType(),
                    ),
                    $transform->getSpan(),
                );
            }

            return $result;
        });
    }
}
