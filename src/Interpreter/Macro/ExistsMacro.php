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
 * Implements the `exists()` macro.
 *
 * The `exists` macro checks whether a predicate holds for at least one element
 * of a list or map. It has a single-variable form (`list.exists(x, p)`) and a
 * two-variable form binding the index/key and the value (`list.exists(i, v, p)`).
 *
 * @example list.exists(x, x > 0)
 * @example [1, 2].exists(i, v, i == 1 && v == 2)
 *
 * @internal
 */
final readonly class ExistsMacro implements MacroInterface
{
    use ComprehensionSupport;

    #[Override]
    public function getName(): string
    {
        return 'exists';
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
        $bindings = self::comprehensionBindings('exists', $call, $context, $variableCount);

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
                        sprintf('The `exists` macro predicate must result in a boolean, got `%s`', $result->getType()),
                        $callback->getSpan(),
                    );
                }

                if ($result->value) {
                    return new BooleanValue(true);
                }
            }

            if (null !== $pendingError) {
                throw $pendingError;
            }

            return new BooleanValue(false);
        });
    }
}
