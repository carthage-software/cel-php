<?php

declare(strict_types=1);

namespace Cel\Extension\Core\Function\Handler\Int;

use Cel\Exception\InternalException;
use Cel\Function\FunctionOverloadHandlerInterface;
use Cel\Syntax\Member\CallExpression;
use Cel\Util\ArgumentsUtil;
use Cel\Value\IntegerValue;
use Cel\Value\TimestampValue;
use Cel\Value\Value;
use Override;

/**
 * Handles int(timestamp) -> integer
 *
 * Returns the number of seconds since the Unix epoch.
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
        $value = ArgumentsUtil::get($arguments, 0, TimestampValue::class);

        return new IntegerValue($value->value->getSeconds());
    }
}
