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
final readonly class FilterMacro implements MacroInterface
{
    #[Override]
    public function getName(): string
    {
        return 'filter';
    }

    #[Override]
    public function canHandle(CallExpression $call): bool
    {
        if (null === $call->target) {
            return false;
        }

        return 2 === $call->arguments->count();
    }

    #[Override]
    public function execute(CallExpression $call, MacroContextInterface $context): Value
    {
        $call_target = $call->target;
        assert(null !== $call_target, 'filter() macro requires a target');

        $name = $call->arguments->at(0);
        $callback = $call->arguments->at(1);

        if (!$name instanceof IdentifierExpression) {
            throw new InvalidMacroCallException(
                'The `filter` macro requires the first argument to be an identifier.',
                $name->getSpan(),
            );
        }

        $target = $context->evaluate($call_target);
        if (!$target instanceof ListValue && !$target instanceof MapValue) {
            throw new InvalidMacroCallException(
                sprintf('The `filter` macro requires a list or map target, got `%s`', $target->getType()),
                $call_target->getSpan(),
            );
        }

        $variableName = $name->identifier->name;
        $environment = $context->getEnvironment()->fork();

        /** @var ListValue */
        return $context->withEnvironment($environment, static function () use (
            $target,
            $variableName,
            $callback,
            $context,
            $environment,
        ): ListValue {
            $results = [];
            $items = $target instanceof ListValue
                ? $target->value
                : array_map(MapKeyUtil::keyToValue(...), array_keys($target->value));

            foreach ($items as $item) {
                $environment->addVariable($variableName, $item);

                $filterResult = $context->evaluate($callback);
                if (!$filterResult instanceof BooleanValue) {
                    throw new InvalidMacroCallException(
                        sprintf(
                            'The `filter` macro predicate must result in a boolean, got `%s`',
                            $filterResult->getType(),
                        ),
                        $callback->getSpan(),
                    );
                }

                if ($filterResult->value) {
                    $results[] = $item;
                }
            }

            return new ListValue($results);
        });
    }
}
