<?php

declare(strict_types=1);

namespace Cel\Interpreter\Macro;

use Cel\Exception\EvaluationException;
use Cel\Exception\InvalidMacroCallException;
use Cel\Syntax\Member\CallExpression;
use Cel\Value\BooleanValue;
use Cel\Value\Value;
use Override;

use function sprintf;

/**
 * Implements the two-variable `existsOne()` macro.
 *
 * Returns true when the predicate holds for exactly one entry, binding the
 * index/key as the first variable and the value as the second
 * (`list.existsOne(i, v, p)`, `map.existsOne(k, v, p)`). Unlike `all`/`exists`,
 * every entry is evaluated, so a predicate error is not absorbed.
 *
 * @example [5, 7, 8].existsOne(i, v, v % 5 == i)
 *
 * @internal
 */
final readonly class ExistsOneTwoVarMacro implements MacroInterface
{
    use ComprehensionSupport;

    #[Override]
    public function getName(): string
    {
        return 'existsOne';
    }

    #[Override]
    public function canHandle(CallExpression $call): bool
    {
        return null !== $call->target && $call->arguments->count() === 3;
    }

    #[Override]
    public function execute(CallExpression $call, MacroContextInterface $context): Value
    {
        // @mago-expect analysis:unhandled-thrown-type - the argument count is guaranteed by canHandle().
        $callback = $call->arguments->at(2);
        $bindings = self::comprehensionBindings('existsOne', $call, $context, 2);

        $environment = $context->getEnvironment()->fork();

        /** @var BooleanValue */
        return $context->withEnvironment(
            $environment,
            /**
             * @throws EvaluationException If evaluating the callback fails.
             * @throws InvalidMacroCallException If the callback result is invalid.
             */
            static function () use ($bindings, $callback, $context, $environment): BooleanValue {
                $trueCount = 0;
                foreach ($bindings as $variables) {
                    foreach ($variables as $variable => $value) {
                        $environment->addVariable($variable, $value);
                    }

                    $result = $context->evaluate($callback);
                    if (!$result instanceof BooleanValue) {
                        throw new InvalidMacroCallException(
                            sprintf(
                                'The `existsOne` macro predicate must result in a boolean, got `%s`',
                                $result->getType(),
                            ),
                            $callback->getSpan(),
                        );
                    }

                    if ($result->value) {
                        ++$trueCount;
                    }
                }

                return new BooleanValue(1 === $trueCount);
            },
        );
    }
}
