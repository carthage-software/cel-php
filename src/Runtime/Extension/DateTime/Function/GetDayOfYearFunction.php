<?php

declare(strict_types=1);

namespace Cel\Runtime\Extension\DateTime\Function;

use Cel\Runtime\Exception\RuntimeException;
use Cel\Runtime\Function\FunctionInterface;
use Cel\Runtime\Value\IntegerValue;
use Cel\Runtime\Value\StringValue;
use Cel\Runtime\Value\TimestampValue;
use Cel\Runtime\Value\Value;
use Cel\Runtime\Value\ValueKind;
use Cel\Syntax\Member\CallExpression;
use Override;
use Psl\DateTime\DateTime;
use Psl\DateTime\Month;
use Psl\DateTime\Timezone;
use Psl\Str;

final readonly class GetDayOfYearFunction implements FunctionInterface
{
    #[Override]
    public function getName(): string
    {
        return 'getDayOfYear';
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
                        throw new RuntimeException(
                            Str\format('getDayOfYear: timezone `%s` is not valid', $timezoneArg->value),
                            $call->getSpan(),
                        );
                    }

                    $timezone = $tz;
                }

                $datetime = DateTime::fromTimestamp($timestamp->value, $timezone);
                $month = $datetime->getMonth();
                $number_of_days = $datetime->getDay();
                while ($month > 1) {
                    $number_of_days += Month::from($month)->getDaysForYear($datetime->getYear());
                    $month--;
                }

                // Psl is 1-based, CEL spec is 0-based.
                return new IntegerValue($number_of_days - 1);
            };

        yield [ValueKind::Timestamp] => $handler;
        yield [ValueKind::Timestamp, ValueKind::String] => $handler;
    }
}
