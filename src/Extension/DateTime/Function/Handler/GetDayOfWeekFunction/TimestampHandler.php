<?php

declare(strict_types=1);

namespace Cel\Extension\DateTime\Function\Handler\GetDayOfWeekFunction;

use Cel\Exception\EvaluationException;
use Cel\Exception\InternalException;
use Cel\Function\FunctionOverloadHandlerInterface;
use Cel\Syntax\Member\CallExpression;
use Cel\Util\ArgumentsUtil;
use Cel\Util\TimezoneUtil;
use Cel\Value\IntegerValue;
use Cel\Value\StringValue;
use Cel\Value\TimestampValue;
use Cel\Value\Value;
use Override;
use Psl\DateTime\DateTime;
use Psl\DateTime\Timezone;

use function sprintf;

/**
 * Handles getDayOfWeek(timestamp) and getDayOfWeek(timestamp, string) -> int
 *
 * @internal
 */
final readonly class TimestampHandler implements FunctionOverloadHandlerInterface
{
    /**
     * @param CallExpression $call The call expression.
     * @param list<Value> $arguments The function arguments.
     *
     * @return Value The resulting value.
     *
     * @throws EvaluationException If the timezone argument is not valid.
     * @throws InternalException If argument type assertion fails.
     */
    #[Override]
    public function __invoke(CallExpression $call, array $arguments): Value
    {
        $timestamp = ArgumentsUtil::get($arguments, 0, TimestampValue::class);
        $timezoneArg = ArgumentsUtil::getOptional($arguments, 1, StringValue::class);

        if (null === $timezoneArg) {
            $datetime = DateTime::fromTimestamp($timestamp->value, Timezone::UTC);
        } else {
            $datetime = TimezoneUtil::localize($timestamp->value, $timezoneArg->value);
            if (null === $datetime) {
                throw new EvaluationException(
                    sprintf('getDayOfWeek: timezone `%s` is not valid', $timezoneArg->value),
                    $call->getSpan(),
                );
            }
        }

        return new IntegerValue($datetime->getWeekday()->value % 7);
    }
}
