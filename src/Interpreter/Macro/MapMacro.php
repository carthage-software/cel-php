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

final readonly class MapMacro implements MacroInterface
{
    #[Override]
    public function getName(): string
    {
        return 'map';
    }

    #[Override]
    public function canHandle(CallExpression $call): bool
    {
        if (null === $call->target) {
            return false;
        }

        $argCount = $call->arguments->count();
        if ($argCount < 2 || $argCount > 3) {
            return false;
        }

        return true;
    }

    #[Override]
    public function execute(CallExpression $call, MacroContextInterface $context): Value
    {
        assert(null !== $call->target, 'map() macro requires a target');

        $name = $call->arguments->elements[0];

        if (!$name instanceof IdentifierExpression) {
            throw new InvalidMacroCallException(
                'The `map` macro requires the first argument to be an identifier.',
                $name->getSpan(),
            );
        }

        $target = $context->evaluate($call->target);
        if (!$target instanceof ListValue && !$target instanceof MapValue) {
            throw new InvalidMacroCallException(
                Str\format('The `map` macro requires a list or map target, got `%s`', $target->getType()),
                $call->target->getSpan(),
            );
        }

        $variableName = $name->identifier->name;
        $environment = $context->getEnvironment()->fork();

        /** @var ListValue $result */
        $result = $context->withEnvironment($environment, static function () use (
            $call,
            $target,
            $variableName,
            $context,
            $environment,
        ): ListValue {
            $results = [];
            $argCount = $call->arguments->count();

            $filterCallback = 3 === $argCount ? $call->arguments->elements[1] : null;
            $transformCallback = 3 === $argCount ? $call->arguments->elements[2] : $call->arguments->elements[1];

            $items = $target instanceof ListValue
                ? $target->value
                : Vec\map(Vec\keys($target->value), Value::from(...));

            foreach ($items as $item) {
                $environment->addVariable($variableName, $item);

                if (null !== $filterCallback) {
                    $filterResult = $context->evaluate($filterCallback);
                    if (!$filterResult instanceof BooleanValue) {
                        throw new InvalidMacroCallException(
                            Str\format(
                                'The `map` macro filter must result in a boolean, got `%s`',
                                $filterResult->getType(),
                            ),
                            $filterCallback->getSpan(),
                        );
                    }
                    if (!$filterResult->value) {
                        continue;
                    }
                }

                $results[] = $context->evaluate($transformCallback);
            }

            return new ListValue($results);
        });

        return $result;
    }
}
