<?php

declare(strict_types=1);

namespace Cel\Interpreter\Macro;

use Cel\Exception\InvalidMacroCallException;
use Cel\Syntax\Member\CallExpression;
use Cel\Syntax\Member\MemberAccessExpression;
use Cel\Util\MapKeyUtil;
use Cel\Value\BooleanValue;
use Cel\Value\MapValue;
use Cel\Value\MessageValue;
use Cel\Value\OptionalValue;
use Cel\Value\Value;
use Override;

use function sprintf;

/**
 * Implements the `has()` macro.
 *
 * The `has` macro checks if a message has a specific field or if a map
 * contains a specific key.
 *
 * @example has(message.field)
 * @example has(map.key)
 *
 * @internal
 */
final readonly class HasMacro implements MacroInterface
{
    #[Override]
    public function getName(): string
    {
        return 'has';
    }

    #[Override]
    public function canHandle(CallExpression $call): bool
    {
        // has() must be called as a function (no target)
        if (null !== $call->target) {
            return false;
        }

        // Must have exactly one argument
        return 1 === $call->arguments->count();
    }

    #[Override]
    public function execute(CallExpression $call, MacroContextInterface $context): Value
    {
        $argument = $call->arguments->at(0);

        if (!$argument instanceof MemberAccessExpression) {
            throw new InvalidMacroCallException(
                'The `has` macro requires a single member access expression as an argument.',
                $argument->getSpan(),
            );
        }

        $operand = $context->evaluate($argument->operand);
        if ($operand instanceof OptionalValue) {
            if (null === $operand->value) {
                return new BooleanValue(false);
            }

            $operand = $operand->value;
        }

        if (!$operand instanceof MessageValue && !$operand instanceof MapValue) {
            throw new InvalidMacroCallException(
                sprintf('The `has` macro requires a message or map operand, got `%s`', $operand->getType()),
                $argument->operand->getSpan(),
            );
        }

        if ($operand instanceof MessageValue) {
            return new BooleanValue($operand->hasField($argument->field->name));
        }

        return new BooleanValue($operand->has(MapKeyUtil::stringKey($argument->field->name)));
    }
}
