<?php

declare(strict_types=1);

namespace Cel\Runtime\Extension\DateTime\Function;

use Cel\Runtime\Exception\TypeConversionException;
use Cel\Runtime\Function\FunctionInterface;
use Cel\Runtime\Value\DurationValue;
use Cel\Runtime\Value\StringValue;
use Cel\Runtime\Value\Value;
use Cel\Runtime\Value\ValueKind;
use Cel\Syntax\Member\CallExpression;
use Override;
use Psl\DateTime;
use Psl\DateTime\Duration;
use Psl\Regex;
use Psl\Str;
use Psl\Type;

final readonly class DurationFunction implements FunctionInterface
{
    // Regex to parse CEL duration format, e.g., "-1h30m5.5s"
    private const string DURATION_PATTERN = '/^([+-])?(?:(\d+)h)?(?:(\d+)m)?(?:(\d+(?:\.\d*)?)s)?(?:(\d+)ms)?(?:(\d+)us)?(?:(\d+)ns)?$/';

    #[Override]
    public function getName(): string
    {
        return 'duration';
    }

    #[Override]
    public function isIdempotent(): bool
    {
        return true;
    }

    #[Override]
    public function getOverloads(): iterable
    {
        yield [ValueKind::String] =>
            /**
             * @param CallExpression $call      The call expression representing the function call.
             * @param list<Value>    $arguments The arguments passed to the function.
             */
            static function (CallExpression $call, array $arguments): DurationValue {
                /** @var StringValue $value */
                $value = $arguments[0];
                $durationStr = $value->value;

                $matches = Regex\first_match($durationStr, self::DURATION_PATTERN);
                if (null === $matches) {
                    throw new TypeConversionException(
                        Str\format('Invalid duration format: "%s"', $durationStr),
                        $call->getSpan(),
                    );
                }

                $negate = ($matches[1] ?? '+') === '-' ? true : false;
                $hours = (int) ($matches[2] ?? 0);
                $minutes = (int) ($matches[3] ?? 0);
                $secondsWithFraction = (float) ($matches[4] ?? 0.0); // @mago-expect analysis:invalid-type-cast (we know it's a valid float)
                $milliseconds = (int) ($matches[5] ?? 0);
                $microseconds = (int) ($matches[6] ?? 0);
                $nanoseconds = (int) ($matches[7] ?? 0);

                $totalNanoseconds = (int) (
                    ($secondsWithFraction - (int) $secondsWithFraction)
                    * DateTime\NANOSECONDS_PER_SECOND
                );
                $totalNanoseconds += $milliseconds * DateTime\MICROSECONDS_PER_SECOND;
                $totalNanoseconds += $microseconds * DateTime\NANOSECONDS_PER_MICROSECOND;
                $totalNanoseconds += $nanoseconds;

                if ($negate) {
                    $hours = -$hours;
                    $minutes = -$minutes;
                    $secondsWithFraction = -$secondsWithFraction;
                    $totalNanoseconds = -$totalNanoseconds;
                }

                $duration = Duration::fromParts($hours, $minutes, (int) $secondsWithFraction, $totalNanoseconds);

                return new DurationValue($duration);
            };
    }
}
