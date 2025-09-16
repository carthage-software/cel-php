<?php

declare(strict_types=1);

namespace Cel\Runtime\Extension\DateTime\Function;

use Cel\Runtime\Exception\EvaluationException;
use Cel\Runtime\Function\FunctionInterface;
use Cel\Runtime\Value\DurationValue;
use Cel\Runtime\Value\IntegerValue;
use Cel\Runtime\Value\StringValue;
use Cel\Runtime\Value\TimestampValue;
use Cel\Runtime\Value\Value;
use Cel\Runtime\Value\ValueKind;
use Cel\Syntax\Member\CallExpression;
use Override;
use Psl\DateTime;
use Psl\DateTime\Timezone;
use Psl\Str;

/**
 * @mago-expect analysis:unused-parameter
 */
final readonly class GetMillisecondsFunction implements FunctionInterface
{
    #[Override]
    public function getName(): string
    {
        return 'getMilliseconds';
    }

    #[Override]
    public function isIdempotent(): bool
    {
        return true;
    }

    #[Override]
    public function getOverloads(): iterable
    {
        yield [ValueKind::Duration] =>
            /**
             * @param CallExpression $call      The call expression representing the function call.
             * @param list<Value>    $arguments The arguments passed to the function.
             */
            static function (CallExpression $call, array $arguments): IntegerValue {
                /** @var DurationValue $duration */
                $duration = $arguments[0];

                return new IntegerValue((int) $duration->value->getTotalMilliseconds());
            };

        yield [ValueKind::Timestamp] =>
            /**
             * @param CallExpression $call      The call expression representing the function call.
             * @param list<Value>    $arguments The arguments passed to the function.
             */
            static function (CallExpression $call, array $arguments): IntegerValue {
                /** @var TimestampValue $timestamp */
                $timestamp = $arguments[0];

                $datetime = DateTime\DateTime::fromTimestamp($timestamp->value, Timezone::UTC);
                $nanoseconds = $datetime->getNanoseconds();
                $milliseconds = (int) ($nanoseconds / DateTime\NANOSECONDS_PER_MILLISECOND);

                return new IntegerValue($milliseconds);
            };

        yield [ValueKind::Timestamp, ValueKind::String] =>
            /**
             * @param CallExpression $call      The call expression representing the function call.
             * @param list<Value>    $arguments The arguments passed to the function.
             */
            static function (CallExpression $call, array $arguments): IntegerValue {
                /** @var TimestampValue $timestamp */
                $timestamp = $arguments[0];
                /** @var StringValue $timezoneArg */
                $timezoneArg = $arguments[1];

                $timezone = Timezone::tryFrom($timezoneArg->value);
                if ($timezone === null) {
                    throw new EvaluationException(
                        Str\format('getHours: timezone `%s` is not valid', $timezoneArg->value),
                        $call->getSpan(),
                    );
                }

                $datetime = DateTime\DateTime::fromTimestamp($timestamp->value, $timezone);
                $nanoseconds = $datetime->getNanoseconds();
                $milliseconds = (int) ($nanoseconds / DateTime\NANOSECONDS_PER_MILLISECOND);

                return new IntegerValue($milliseconds);
            };
    }
}
