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
use Psl\DateTime\DateTime;
use Psl\DateTime\Timezone;
use Psl\Str;

/**
 * @mago-expect analysis:unused-parameter
 */
final readonly class GetMinutesFunction implements FunctionInterface
{
    #[Override]
    public function getName(): string
    {
        return 'getMinutes';
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

                return new IntegerValue((int) $duration->value->getTotalMinutes());
            };

        yield [ValueKind::Timestamp] =>
            /**
             * @param CallExpression $call      The call expression representing the function call.
             * @param list<Value>    $arguments The arguments passed to the function.
             */
            static function (CallExpression $call, array $arguments): IntegerValue {
                /** @var TimestampValue $timestamp */
                $timestamp = $arguments[0];

                $datetime = DateTime::fromTimestamp($timestamp->value, Timezone::UTC);

                return new IntegerValue($datetime->getMinutes());
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
                if (null === $timezone) {
                    throw new EvaluationException(
                        Str\format('getHours: timezone `%s` is not valid', $timezoneArg->value),
                        $call->getSpan(),
                    );
                }

                $datetime = DateTime::fromTimestamp($timestamp->value, $timezone);

                return new IntegerValue($datetime->getMinutes());
            };
    }
}
