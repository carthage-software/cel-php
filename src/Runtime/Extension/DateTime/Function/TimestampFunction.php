<?php

declare(strict_types=1);

namespace Cel\Runtime\Extension\DateTime\Function;

use Cel\Runtime\Exception\TypeConversionException;
use Cel\Runtime\Function\FunctionInterface;
use Cel\Runtime\Value\FloatValue;
use Cel\Runtime\Value\IntegerValue;
use Cel\Runtime\Value\StringValue;
use Cel\Runtime\Value\TimestampValue;
use Cel\Runtime\Value\UnsignedIntegerValue;
use Cel\Runtime\Value\Value;
use Cel\Runtime\Value\ValueKind;
use Cel\Syntax\Member\CallExpression;
use Override;
use Psl\DateTime;
use Psl\DateTime\Exception\RuntimeException;
use Psl\DateTime\FormatPattern;
use Psl\DateTime\Timestamp;
use Psl\DateTime\Timezone;
use Psl\Math;
use Psl\Regex;
use Psl\Str;

use function bccomp;
use function is_string;

final readonly class TimestampFunction implements FunctionInterface
{
    private const string RFC3339_PATTERN = '/^(\d{4}-\d{2}-\d{2}T\d{2}:\d{2}:\d{2})(?:\.(\d+))?(.*)$/';

    #[Override]
    public function getName(): string
    {
        return 'timestamp';
    }

    #[Override]
    public function isIdempotent(): bool
    {
        return true;
    }

    #[Override]
    public function getOverloads(): iterable
    {
        yield [ValueKind::Integer] => static function (CallExpression $_expr, array $arguments): TimestampValue {
            /** @var IntegerValue $seconds */
            $seconds = $arguments[0];

            return new TimestampValue(Timestamp::fromParts($seconds->value));
        };

        yield [ValueKind::Float] => static function (CallExpression $_expr, array $arguments): TimestampValue {
            /** @var FloatValue $seconds */
            $seconds = $arguments[0];

            $wholeSeconds = (int) $seconds->value;
            $nanoseconds = (int) (($seconds->value - $wholeSeconds) * DateTime\NANOSECONDS_PER_SECOND);

            return new TimestampValue(Timestamp::fromParts($wholeSeconds, $nanoseconds));
        };

        yield [ValueKind::String] =>
            /**
             * @param CallExpression $call      The call expression representing the function call.
             * @param list<Value>    $arguments The arguments passed to the function.
             */
            static function (CallExpression $call, array $arguments): TimestampValue {
                /** @var StringValue $value */
                $value = $arguments[0];
                $timestampString = $value->value;

                try {
                    $parts = Regex\first_match($timestampString, self::RFC3339_PATTERN);
                    if ($parts === null) {
                        throw new TypeConversionException(
                            Str\format('Failed to parse timestamp string "%s".', $timestampString),
                            $call->getSpan(),
                        );
                    }

                    $mainPart = $parts[1] . ($parts[3] ?? '');
                    $fractionalPart = $parts[2] ?? '0';

                    // Parse the main part of the timestamp (without fractional seconds).
                    $baseTimestamp = Timestamp::parse(
                        $mainPart,
                        FormatPattern::Rfc3339WithoutMicroseconds,
                        Timezone::UTC,
                    );

                    // Normalize the fractional part to nanoseconds.
                    $nanosStr = Str\pad_right($fractionalPart, 9, '0');
                    $nanosStr = Str\slice($nanosStr, 0, 9);
                    $nanoseconds = Str\to_int($nanosStr);

                    // Combine the base timestamp with the parsed nanoseconds.
                    $finalTimestamp = Timestamp::fromParts($baseTimestamp->getSeconds(), $nanoseconds ?? 0);

                    return new TimestampValue($finalTimestamp);
                } catch (RuntimeException) {
                    throw new TypeConversionException(
                        Str\format('Failed to parse timestamp string "%s".', $timestampString),
                        $call->getSpan(),
                    );
                }
            };
    }
}
