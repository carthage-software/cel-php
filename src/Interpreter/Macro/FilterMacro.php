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
        assert(null !== $call->target, 'filter() macro requires a target');

        $name = $call->arguments->elements[0];
        $callback = $call->arguments->elements[1];

        if (!$name instanceof IdentifierExpression) {
            throw new InvalidMacroCallException(
                'The `filter` macro requires the first argument to be an identifier.',
                $name->getSpan(),
            );
        }

        $target = $context->evaluate($call->target);
        if (!$target instanceof ListValue && !$target instanceof MapValue) {
            throw new InvalidMacroCallException(
                Str\format('The `filter` macro requires a list or map target, got `%s`', $target->getType()),
                $call->target->getSpan(),
            );
        }

        $variableName = $name->identifier->name;
        $environment = $context->getEnvironment()->fork();

        /** @var ListValue $result */
        $result = $context->withEnvironment($environment, static function () use (
            $target,
            $variableName,
            $callback,
            $context,
            $environment,
        ): ListValue {
            $results = [];
            $items = $target instanceof ListValue
                ? $target->value
                : Vec\map(Vec\keys($target->value), Value::from(...));

            foreach ($items as $item) {
                $environment->addVariable($variableName, $item);

                $filterResult = $context->evaluate($callback);
                if (!$filterResult instanceof BooleanValue) {
                    throw new InvalidMacroCallException(
                        Str\format(
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

        return $result;
    }
}
