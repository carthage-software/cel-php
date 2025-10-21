<?php

declare(strict_types=1);

namespace Cel\Extension\DateTime\Function\Handler\GetSecondsFunction;

use Cel\Exception\EvaluationException;
use Cel\Exception\InternalException;
use Cel\Function\FunctionOverloadHandlerInterface;
use Cel\Syntax\Member\CallExpression;
use Cel\Util\ArgumentsUtil;
use Cel\Value\IntegerValue;
use Cel\Value\StringValue;
use Cel\Value\TimestampValue;
use Cel\Value\Value;
use Override;
use Psl\DateTime\DateTime;
use Psl\DateTime\Timezone;
use Psl\Exception\InvariantViolationException;
use Psl\Str;

final readonly class TimestampWithTimezoneHandler implements FunctionOverloadHandlerInterface
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
        $timezoneArg = ArgumentsUtil::get($arguments, 1, StringValue::class);

        $timezone = Timezone::tryFrom($timezoneArg->value);
        if (null === $timezone) {
            throw new EvaluationException(
                Str\format('getHours: timezone `%s` is not valid', $timezoneArg->value),
                $call->getSpan(),
            );
        }

        try {
            $datetime = DateTime::fromTimestamp($timestamp->value, $timezone);

            return new IntegerValue($datetime->getSeconds());
        } catch (InvariantViolationException $e) {
            throw new EvaluationException(Str\format('Operation failed: %s', $e->getMessage()), $call->getSpan(), $e);
        }
    }
}
