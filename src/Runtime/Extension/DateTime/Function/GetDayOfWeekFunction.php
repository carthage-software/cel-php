<?php

declare(strict_types=1);

namespace Cel\Runtime\Extension\DateTime\Function;

use Cel\Runtime\Exception\EvaluationException;
use Cel\Runtime\Function\FunctionInterface;
use Cel\Runtime\Value\IntegerValue;
use Cel\Runtime\Value\StringValue;
use Cel\Runtime\Value\TimestampValue;
use Cel\Runtime\Value\Value;
use Cel\Runtime\Value\ValueKind;
use Cel\Syntax\Member\CallExpression;
use Override;
use Psl\DateTime\DateTime;
use Psl\DateTime\Timezone;
use Psl\Str;

final readonly class GetDayOfWeekFunction implements FunctionInterface
{
    #[Override]
    public function getName(): string
    {
        return 'getDayOfWeek';
    }

    #[Override]
    public function isIdempotent(): bool
    {
        return true;
    }

    #[Override]
    public function getOverloads(): iterable
    {
        $handler =
            /**
             * @param CallExpression $call      The call expression representing the function call.
             * @param list<Value>    $arguments The arguments passed to the function.
             */
            static function (CallExpression $call, array $arguments): IntegerValue {
                /** @var TimestampValue $timestamp */
                $timestamp = $arguments[0];
                /** @var StringValue|null $timezoneArg */
                $timezoneArg = $arguments[1] ?? null;

                $timezone = Timezone::UTC;
                if ($timezoneArg !== null) {
                    $tz = Timezone::tryFrom($timezoneArg->value);
                    if ($tz === null) {
                        throw new EvaluationException(
                            Str\format('getDayOfWeek: timezone `%s` is not valid', $timezoneArg->value),
                            $call->getSpan(),
                        );
                    }
                    $timezone = $tz;
                }

                $datetime = DateTime::fromTimestamp($timestamp->value, $timezone);
                $pslWeekday = $datetime->getWeekday()->value;

                // Psl Weekday: Monday=1, ..., Sunday=7.
                // CEL Weekday: Sunday=0, Monday=1, ..., Saturday=6.
                return new IntegerValue($pslWeekday % 7);
            };

        yield [ValueKind::Timestamp] => $handler;
        yield [ValueKind::Timestamp, ValueKind::String] => $handler;
    }
}
