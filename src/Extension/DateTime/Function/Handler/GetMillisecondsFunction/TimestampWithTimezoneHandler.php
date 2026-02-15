<?php

declare(strict_types=1);

namespace Cel\Extension\DateTime\Function\Handler\GetMillisecondsFunction;

use Cel\Exception\EvaluationException;
use Cel\Exception\InternalException;
use Cel\Function\FunctionOverloadHandlerInterface;
use Cel\Span\Span;
use Cel\Util\ArgumentsUtil;
use Cel\Value\IntegerValue;
use Cel\Value\StringValue;
use Cel\Value\TimestampValue;
use Cel\Value\Value;
use Override;
use Psl\DateTime;
use Psl\DateTime\Timezone;
use Psl\Exception\InvariantViolationException;
use Psl\Str;

final readonly class TimestampWithTimezoneHandler implements FunctionOverloadHandlerInterface
{
    /**
     * @param Span $span The call expression.
     * @param list<Value> $arguments The function arguments.
     *
     * @return Value The resulting value.
     *
     * @throws EvaluationException If the operation fails.
     * @throws InternalException If an internal error occurs.
     */
    #[Override]
    public function __invoke(Span $span, array $arguments): Value
    {
        $timestamp = ArgumentsUtil::get($arguments, 0, TimestampValue::class);
        $timezoneArg = ArgumentsUtil::get($arguments, 1, StringValue::class);

        $timezone = Timezone::tryFrom($timezoneArg->value);
        if (null === $timezone) {
            throw new EvaluationException(
                Str\format('getHours: timezone `%s` is not valid', $timezoneArg->value),
                $span,
            );
        }

        try {
            $datetime = DateTime\DateTime::fromTimestamp($timestamp->value, $timezone);
            $nanoseconds = $datetime->getNanoseconds();
            $milliseconds = (int) ($nanoseconds / DateTime\NANOSECONDS_PER_MILLISECOND);

            return new IntegerValue($milliseconds);
        } catch (InvariantViolationException $e) {
            throw new EvaluationException(Str\format('Operation failed: %s', $e->getMessage()), $span, $e);
        }
    }
}
