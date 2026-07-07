<?php

declare(strict_types=1);

namespace Cel\Interpreter\Macro;

use Cel\Exception\InvalidMacroCallException;
use Cel\Syntax\Member\CallExpression;
use Cel\Syntax\Member\IdentifierExpression;
use Cel\Util\MapKeyUtil;
use Cel\Value\BooleanValue;
use Cel\Value\ListValue;
use Cel\Value\MapValue;
use Cel\Value\Value;
use Override;

use function array_keys;
use function array_map;
use function assert;
use function sprintf;

/**
 * @internal
 */
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
        $call_target = $call->target;
        assert(null !== $call_target, 'map() macro requires a target');

        $name = $call->arguments->at(0);

        if (!$name instanceof IdentifierExpression) {
            throw new InvalidMacroCallException(
                'The `map` macro requires the first argument to be an identifier.',
                $name->getSpan(),
            );
        }

        $target = $context->evaluate($call_target);
        if (!$target instanceof ListValue && !$target instanceof MapValue) {
            throw new InvalidMacroCallException(
                sprintf('The `map` macro requires a list or map target, got `%s`', $target->getType()),
                $call_target->getSpan(),
            );
        }

        $variableName = $name->identifier->name;
        $environment = $context->getEnvironment()->fork();

        /** @var ListValue */
        return $context->withEnvironment($environment, static function () use (
            $call,
            $target,
            $variableName,
            $context,
            $environment,
        ): ListValue {
            $results = [];
            $argCount = $call->arguments->count();

            $filterCallback = 3 === $argCount ? $call->arguments->at(1) : null;
            $transformCallback = 3 === $argCount ? $call->arguments->at(2) : $call->arguments->at(1);

            $items = $target instanceof ListValue
                ? $target->value
                : array_map(MapKeyUtil::keyToValue(...), array_keys($target->value));

            foreach ($items as $item) {
                $environment->addVariable($variableName, $item);

                if (null !== $filterCallback) {
                    $filterResult = $context->evaluate($filterCallback);
                    if (!$filterResult instanceof BooleanValue) {
                        throw new InvalidMacroCallException(
                            sprintf(
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
    }
}
