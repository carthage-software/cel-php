<?php

declare(strict_types=1);

namespace Cel\Extension\DateTime\Function\Handler\TimestampFunction;

use Cel\Exception\InternalException;
use Cel\Function\FunctionOverloadHandlerInterface;
use Cel\Syntax\Member\CallExpression;
use Cel\Util\ArgumentsUtil;
use Cel\Value\TimestampValue;
use Cel\Value\Value;
use Override;

/**
 * Handles timestamp(timestamp) -> timestamp
 *
 * The identity overload: returns the timestamp unchanged.
 */
final readonly class FromTimestampHandler implements FunctionOverloadHandlerInterface
{
    /**
     * @param CallExpression $call The call expression.
     * @param list<Value> $arguments The function arguments.
     *
     * @return Value The resulting value.
     *
     * @throws InternalException If argument type assertion fails.
     */
    #[Override]
    public function __invoke(CallExpression $call, array $arguments): Value
    {
        return ArgumentsUtil::get($arguments, 0, TimestampValue::class);
    }
}
