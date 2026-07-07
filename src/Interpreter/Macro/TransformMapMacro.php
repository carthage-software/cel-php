<?php

declare(strict_types=1);

namespace Cel\Interpreter\Macro;

use Cel\Exception\InvalidMacroCallException;
use Cel\Syntax\Member\CallExpression;
use Cel\Util\MapKeyUtil;
use Cel\Value\BooleanValue;
use Cel\Value\MapValue;
use Cel\Value\Value;
use Override;

use function array_key_exists;
use function assert;
use function sprintf;

/**
 * Implements the two-variable `transformMap()` macro.
 *
 * Produces a new map with the same keys, transforming each value while binding
 * the key and the value. An optional filter keeps only the entries for which it
 * holds: `map.transformMap(k, v, transform)` or
 * `map.transformMap(k, v, filter, transform)`.
 *
 * @example {'foo': 'bar'}.transformMap(k, v, k + v)  // {'foo': 'foobar'}
 */
final readonly class TransformMapMacro implements MacroInterface
{
    use ComprehensionSupport;

    #[Override]
    public function getName(): string
    {
        return 'transformMap';
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
        $keyVariable = self::iterationVariable('transformMap', $call->arguments->at(0));
        $bindings = self::comprehensionBindings('transformMap', $call, $context, 2);
        $span = $call->getSpan();

        $environment = $context->getEnvironment()->fork();

        /** @var MapValue */
        return $context->withEnvironment($environment, static function () use (
            $bindings,
            $keyVariable,
            $filter,
            $transform,
            $context,
            $environment,
            $span,
        ): MapValue {
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
                                'The `transformMap` macro filter must result in a boolean, got `%s`',
                                $keep->getType(),
                            ),
                            $filter->getSpan(),
                        );
                    }

                    if (!$keep->value) {
                        continue;
                    }
                }

                assert(array_key_exists($keyVariable, $variables));
                $key = MapKeyUtil::resolve($variables[$keyVariable]);
                if (null === $key) {
                    throw new InvalidMacroCallException(
                        'The `transformMap` macro key cannot be represented as a map key.',
                        $span,
                    );
                }

                $results[$key] = $context->evaluate($transform);
            }

            return new MapValue($results);
        });
    }
}
