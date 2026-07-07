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
 * Handles `optional.ofNonZeroValue(T) -> optional(T)`, wrapping the value only when
 * it is not a zero value (otherwise returning `optional.none()`).
 *
 * @internal
 */
final readonly class OfNonZeroValueHandler implements FunctionOverloadHandlerInterface
{
    /**
     * @param CallExpression $call The call expression.
     * @param list<Value> $arguments The function arguments.
     *
     * @return OptionalValue The wrapped value, or an empty optional for zero values.
     *
     * @throws InternalException If argument type assertion fails.
     */
    #[Override]
    public function __invoke(CallExpression $call, array $arguments): OptionalValue
    {
        $value = ArgumentsUtil::get($arguments, 0, Value::class);

        return $value->isZeroValue() ? OptionalValue::none() : OptionalValue::of($value);
    }
}
