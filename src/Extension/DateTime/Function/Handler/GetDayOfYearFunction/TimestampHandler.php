<?php

declare(strict_types=1);

namespace Cel\Extension\DateTime\Function\Handler\GetDayOfYearFunction;

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
use Psl\DateTime\Month;
use Psl\DateTime\Timezone;
use Psl\Str;
use ValueError;

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
                    Str\format('getDayOfYear: timezone `%s` is not valid', $timezoneArg->value),
                    $call->getSpan(),
                );
            }

            $timezone = $tz;
        }

        try {
            $datetime = DateTime::fromTimestamp($timestamp->value, $timezone);
            $month = $datetime->getMonth();
            $number_of_days = $datetime->getDay();
            while ($month > 1) {
                $number_of_days += Month::from($month)->getDaysForYear($datetime->getYear());
                $month--;
            }

            // Psl is 1-based, CEL spec is 0-based.
            return new IntegerValue($number_of_days - 1);
        } catch (ValueError $e) { // @mago-expect analysis:avoid-catching-error
            throw new EvaluationException(Str\format('Operation failed: %s', $e->getMessage()), $call->getSpan(), $e);
        }
    }
}
