<?php

declare(strict_types=1);

namespace Cel\Extension\Optional\Function\Handler;

use Cel\Exception\InternalException;
use Cel\Function\FunctionOverloadHandlerInterface;
use Cel\Syntax\Member\CallExpression;
use Cel\Util\ArgumentsUtil;
use Cel\Value\OptionalValue;
use Cel\Value\Value;
use Override;

/**
 * Handles `optional.of(T) -> optional(T)`, wrapping any value as a present optional.
 *
 * @internal
 */
final readonly class OfHandler implements FunctionOverloadHandlerInterface
{
    /**
     * @param CallExpression $call The call expression.
     * @param list<Value> $arguments The function arguments.
     *
     * @return OptionalValue The wrapped value.
     *
     * @throws InternalException If argument type assertion fails.
     */
    #[Override]
    public function __invoke(CallExpression $call, array $arguments): OptionalValue
    {
        $value = ArgumentsUtil::get($arguments, 0, Value::class);

        return OptionalValue::of($value);
    }
}
