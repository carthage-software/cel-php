<?php

declare(strict_types=1);

namespace Cel\Extension\DateTime\Function\Handler\GetDayOfMonthFunction;

use Cel\Exception\EvaluationException;
use Cel\Exception\InternalException;
use Cel\Function\FunctionOverloadHandlerInterface;
use Cel\Syntax\Member\CallExpression;
use Cel\Util\ArgumentsUtil;
use Cel\Value\IntegerValue;
use Cel\Value\TimestampValue;
use Cel\Value\Value;
use Override;
use Psl\DateTime\DateTime;
use Psl\DateTime\Timezone;
use Psl\Exception\InvariantViolationException;
use Psl\Str;

final readonly class TimestampHandler implements FunctionOverloadHandlerInterface
{
    /**
     * @param CallExpression $call The call expression.
     * @param list<Value> $arguments The function arguments.
     *
     * @return Value The resulting value.
     *
     * @throws EvaluationException If the operation fails.
     * @throws InternalException If an internal error occurs.
     */
    #[Override]
    public function __invoke(CallExpression $call, array $arguments): Value
    {
        $timestamp = ArgumentsUtil::get($arguments, 0, TimestampValue::class);

        try {
            $datetime = DateTime::fromTimestamp($timestamp->value, Timezone::UTC);

            return new IntegerValue($datetime->getDay());
        } catch (InvariantViolationException $e) {
            throw new EvaluationException(Str\format('Operation failed: %s', $e->getMessage()), $call->getSpan(), $e);
        }
    }
}
