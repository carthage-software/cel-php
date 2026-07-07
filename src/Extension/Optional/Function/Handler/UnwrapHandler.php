<?php

declare(strict_types=1);

namespace Cel\Extension\Optional\Function\Handler;

use Cel\Exception\EvaluationException;
use Cel\Exception\InternalException;
use Cel\Function\FunctionOverloadHandlerInterface;
use Cel\Syntax\Member\CallExpression;
use Cel\Util\ArgumentsUtil;
use Cel\Value\ListValue;
use Cel\Value\OptionalValue;
use Cel\Value\Value;
use Override;

use function sprintf;

/**
 * Handles `optional.unwrap(list(optional(T))) -> list(T)` and its postfix form
 * `list(optional(T)).unwrapOpt() -> list(T)`, producing a list containing the
 * values of all present optionals (empty optionals are dropped).
 *
 * @internal
 */
final readonly class UnwrapHandler implements FunctionOverloadHandlerInterface
{
    /**
     * @param CallExpression $call The call expression.
     * @param list<Value> $arguments The function arguments.
     *
     * @return ListValue The values of the present optionals.
     *
     * @throws InternalException If argument type assertion fails.
     * @throws EvaluationException If any list element is not an optional.
     */
    #[Override]
    public function __invoke(CallExpression $call, array $arguments): ListValue
    {
        $list = ArgumentsUtil::get($arguments, 0, ListValue::class);

        $unwrapped = [];
        foreach ($list->value as $element) {
            if (!$element instanceof OptionalValue) {
                throw new EvaluationException(
                    sprintf('unwrap requires a list of optionals, got `%s` element', $element->getType()),
                    $call->getSpan(),
                );
            }

            if (null !== $element->value) {
                $unwrapped[] = $element->value;
            }
        }

        return new ListValue($unwrapped);
    }
}
