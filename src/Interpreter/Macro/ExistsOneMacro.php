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

final readonly class ExistsOneMacro implements MacroInterface
{
    #[Override]
    public function getName(): string
    {
        return 'exists_one';
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
        assert(null !== $call_target, 'exists_one() macro requires a target');

        $name = $call->arguments->at(0);
        $callback = $call->arguments->at(1);

        if (!$name instanceof IdentifierExpression) {
            throw new InvalidMacroCallException(
                'The `exists_one` macro requires the first argument to be an identifier.',
                $name->getSpan(),
            );
        }

        $target = $context->evaluate($call_target);
        if (!$target instanceof ListValue && !$target instanceof MapValue) {
            throw new InvalidMacroCallException(
                sprintf('The `exists_one` macro requires a list or map target, got `%s`', $target->getType()),
                $call_target->getSpan(),
            );
        }

        $items = $target instanceof ListValue
            ? $target->value
            : array_map(MapKeyUtil::keyToValue(...), array_keys($target->value));

        $environment = $context->getEnvironment()->fork();

        /** @var BooleanValue */
        return $context->withEnvironment($environment, static function () use (
            $items,
            $name,
            $callback,
            $context,
            $environment,
        ): BooleanValue {
            $true_count = 0;
            foreach ($items as $value) {
                $environment->addVariable($name->identifier->name, $value);

                $result = $context->evaluate($callback);
                if (!$result instanceof BooleanValue) {
                    throw new InvalidMacroCallException(
                        sprintf(
                            'The `exists_one` macro predicate must result in a boolean, got `%s`',
                            $result->getType(),
                        ),
                        $callback->getSpan(),
                    );
                }

                if ($result->value) {
                    $true_count++;
                }
            }

            return new BooleanValue(1 === $true_count);
        });
    }
}
