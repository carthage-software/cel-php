<?php

declare(strict_types=1);

namespace Cel\Interpreter\Macro;

use Cel\Exception\InvalidMacroCallException;
use Cel\Syntax\Member\CallExpression;
use Cel\Value\BooleanValue;
use Cel\Value\ListValue;
use Cel\Value\Value;
use Override;

use function sprintf;

/**
 * Implements the two-variable `transformList()` macro.
 *
 * Produces a new list by transforming each entry, binding the index/key and the
 * value. An optional filter keeps only the entries for which it holds:
 * `list.transformList(i, v, transform)` or
 * `list.transformList(i, v, filter, transform)`.
 *
 * @example [2, 4, 6].transformList(i, v, v / 2 + i)
 *
 * @internal
 */
final readonly class TransformListMacro implements MacroInterface
{
    use ComprehensionSupport;

    #[Override]
    public function getName(): string
    {
        return 'transformList';
    }

    #[Override]
    public function canHandle(CallExpression $call): bool
    {
        return null !== $call->target && ($call->arguments->count() === 3 || $call->arguments->count() === 4);
    }

    #[Override]
    public function execute(CallExpression $call, MacroContextInterface $context): Value
    {
        $argumentCount = $call->arguments->count();
        $filter = 4 === $argumentCount ? $call->arguments->at(2) : null;
        $transform = $call->arguments->at($argumentCount - 1);
        $bindings = self::comprehensionBindings('transformList', $call, $context, 2);

        $environment = $context->getEnvironment()->fork();

        /** @var ListValue */
        return $context->withEnvironment($environment, static function () use (
            $bindings,
            $filter,
            $transform,
            $context,
            $environment,
        ): ListValue {
            $results = [];
            foreach ($bindings as $variables) {
                foreach ($variables as $variable => $value) {
                    $environment->addVariable($variable, $value);
                }

                if (null !== $filter) {
                    $keep = $context->evaluate($filter);
                    if (!$keep instanceof BooleanValue) {
                        throw new InvalidMacroCallException(
                            sprintf(
                                'The `transformList` macro filter must result in a boolean, got `%s`',
                                $keep->getType(),
                            ),
                            $filter->getSpan(),
                        );
                    }

                    if (!$keep->value) {
                        continue;
                    }
                }

                $results[] = $context->evaluate($transform);
            }

            return new ListValue($results);
        });
    }
}
