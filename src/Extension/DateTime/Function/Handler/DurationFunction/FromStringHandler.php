<?php

declare(strict_types=1);

namespace Cel\Extension\DateTime\Function\Handler\DurationFunction;

use Cel\Exception\EvaluationException;
use Cel\Exception\InternalException;
use Cel\Exception\TypeConversionException;
use Cel\Function\FunctionOverloadHandlerInterface;
use Cel\Syntax\Member\CallExpression;
use Cel\Util\ArgumentsUtil;
use Cel\Value\DurationValue;
use Cel\Value\StringValue;
use Cel\Value\Value;
use Override;
use Psl\DateTime;
use Psl\DateTime\Duration;
use Psl\Exception\ExceptionInterface;
use Psl\Regex;
use Psl\Str;

final readonly class FromStringHandler implements FunctionOverloadHandlerInterface
{
    // Regex to parse CEL duration format, e.g., "-1h30m5.5s"
    private const string DURATION_PATTERN = '/^([+-])?(?:(\d+)h)?(?:(\d+)m)?(?:(\d+(?:\.\d*)?)s)?(?:(\d+)ms)?(?:(\d+)us)?(?:(\d+)ns)?$/';

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
        $value = ArgumentsUtil::get($arguments, 0, StringValue::class);
        $durationStr = $value->value;

        try {
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
        } catch (ExceptionInterface $e) {
            try {
                $message = Str\format('Operation failed: %s', $e->getMessage());
            } catch (ExceptionInterface) {
                $message = 'Operation failed.';
            }

            throw new EvaluationException($message, $call->getSpan(), $e);
        }
    }
}
