<?php

declare(strict_types=1);

namespace Cel\Extension\Optional\Function\Handler;

use Cel\Exception\InternalException;
use Cel\Function\FunctionOverloadHandlerInterface;
use Cel\Syntax\Member\CallExpression;
use Cel\Util\ArgumentsUtil;
use Cel\Value\ListValue;
use Cel\Value\OptionalValue;
use Cel\Value\Value;
use Override;
use Psl\Iter;

/**
 * Handles `list(T).last() -> optional(T)`, returning the last element wrapped in
 * an optional, or `optional.none()` when the list is empty.
 */
final readonly class LastHandler implements FunctionOverloadHandlerInterface
{
    /**
     * @param CallExpression $call The call expression.
     * @param list<Value> $arguments The function arguments.
     *
     * @return OptionalValue The last element, or an empty optional.
     *
     * @throws InternalException If argument type assertion fails.
     */
    #[Override]
    public function __invoke(CallExpression $call, array $arguments): OptionalValue
    {
        $list = ArgumentsUtil::get($arguments, 0, ListValue::class);

        $last = $list->value[Iter\count($list->value) - 1] ?? null;

        return null === $last ? OptionalValue::none() : OptionalValue::of($last);
    }
}
