<?php

declare(strict_types=1);

namespace Cel\Extension\DateTime\Function\Handler\GetDayOfWeekFunction;

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
        $timezoneArg = ArgumentsUtil::getOptional($arguments, 1, StringValue::class);

        $timezone = Timezone::UTC;
        if (null !== $timezoneArg) {
            $tz = Timezone::tryFrom($timezoneArg->value);
            if (null === $tz) {
                throw new EvaluationException(
                    Str\format('getDayOfWeek: timezone `%s` is not valid', $timezoneArg->value),
                    $call->getSpan(),
                );
            }

            $timezone = $tz;
        }

        try {
            $datetime = DateTime::fromTimestamp($timestamp->value, $timezone);
            $pslWeekday = $datetime->getWeekday()->value;

            // Psl Weekday: Monday=1, ..., Sunday=7.
            // CEL Weekday: Sunday=0, Monday=1, ..., Saturday=6.
            return new IntegerValue($pslWeekday % 7);
        } catch (InvariantViolationException $e) {
            throw new EvaluationException(Str\format('Operation failed: %s', $e->getMessage()), $call->getSpan(), $e);
        }
    }
}
