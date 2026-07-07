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
 * Implements the `all()` macro.
 *
 * The `all` macro checks whether a predicate holds for every element of a list
 * or map. It has a single-variable form (`list.all(x, p)`, `map.all(k, p)`) and
 * a two-variable form binding the index/key and the value
 * (`list.all(i, v, p)`, `map.all(k, v, p)`).
 *
 * @example list.all(x, x > 0)
 * @example [1, 2].all(i, v, i < v)
 */
final readonly class AllMacro implements MacroInterface
{
    use ComprehensionSupport;

    #[Override]
    public function getName(): string
    {
        return 'all';
    }

    #[Override]
    public function canHandle(CallExpression $call): bool
    {
        return null !== $call->target && ($call->arguments->count() === 2 || $call->arguments->count() === 3);
    }

    #[Override]
    public function execute(CallExpression $call, MacroContextInterface $context): Value
    {
        $variableCount = $call->arguments->count() - 1;
        $callback = $call->arguments->at($variableCount);
        $bindings = self::comprehensionBindings('all', $call, $context, $variableCount);

        $environment = $context->getEnvironment()->fork();

        /** @var BooleanValue */
        return $context->withEnvironment($environment, static function () use (
            $bindings,
            $callback,
            $context,
            $environment,
        ): BooleanValue {
            $pendingError = null;
            foreach ($bindings as $variables) {
                foreach ($variables as $variable => $value) {
                    $environment->addVariable($variable, $value);
                }

                try {
                    $result = $context->evaluate($callback);
                } catch (EvaluationException $error) {
                    $pendingError ??= $error;

                    continue;
                }

                if (!$result instanceof BooleanValue) {
                    throw new InvalidMacroCallException(
                        sprintf('The `all` macro predicate must result in a boolean, got `%s`', $result->getType()),
                        $callback->getSpan(),
                    );
                }

                if (!$result->value) {
                    return new BooleanValue(false);
                }
            }

            if (null !== $pendingError) {
                throw $pendingError;
            }

            return new BooleanValue(true);
        });
    }
}
