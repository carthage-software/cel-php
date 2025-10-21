<?php

declare(strict_types=1);

namespace Cel\Interpreter\Macro;

use Cel\Exception\InvalidMacroCallException;
use Cel\Syntax\Member\CallExpression;
use Cel\Syntax\Member\IdentifierExpression;
use Cel\Value\BooleanValue;
use Cel\Value\ListValue;
use Cel\Value\MapValue;
use Cel\Value\Value;
use Override;
use Psl\Str;
use Psl\Vec;

use function assert;

final readonly class ExistsMacro implements MacroInterface
{
    #[Override]
    public function getName(): string
    {
        return 'exists';
    }

    #[Override]
    public function canHandle(CallExpression $call): bool
    {
        if (null === $call->target) {
            return false;
        }

        $name = $call->arguments->elements[0] ?? null;
        $callback = $call->arguments->elements[1] ?? null;
        if (null === $name || null === $callback || $call->arguments->count() > 2) {
            return false;
        }

        return true;
    }

    #[Override]
    public function execute(CallExpression $call, MacroContextInterface $context): Value
    {
        assert(null !== $call->target, 'exists() macro requires a target');

        $name = $call->arguments->elements[0];
        $callback = $call->arguments->elements[1];

        if (!$name instanceof IdentifierExpression) {
            throw new InvalidMacroCallException(
                'The `exists` macro requires the first argument to be an identifier.',
                $name->getSpan(),
            );
        }

        $target = $context->evaluate($call->target);
        if (!$target instanceof ListValue && !$target instanceof MapValue) {
            throw new InvalidMacroCallException(
                Str\format('The `exists` macro requires a list or map target, got `%s`', $target->getType()),
                $call->target->getSpan(),
            );
        }

        $items = $target instanceof ListValue ? $target->value : Vec\map(Vec\keys($target->value), Value::from(...));

        $environment = $context->getEnvironment()->fork();
        /** @var BooleanValue $result */
        $result = $context->withEnvironment($environment, function () use (
            $items,
            $name,
            $callback,
            $context,
            $environment,
        ): BooleanValue {
            $found_one = false;
            foreach ($items as $value) {
                $environment->addVariable($name->identifier->name, $value);

                $result = $context->evaluate($callback);
                if (!$result instanceof BooleanValue) {
                    throw new InvalidMacroCallException(
                        Str\format(
                            'The `exists` macro predicate must result in a boolean, got `%s`',
                            $result->getType(),
                        ),
                        $callback->getSpan(),
                    );
                }

                if ($result->value) {
                    $found_one = true;
                    break;
                }
            }

            return new BooleanValue($found_one);
        });

        return $result;
    }
}
