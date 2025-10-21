<?php

declare(strict_types=1);

namespace Cel\Extension\DateTime\Function\Handler\TimestampFunction;

use Cel\Exception\EvaluationException;
use Cel\Exception\InternalException;
use Cel\Exception\TypeConversionException;
use Cel\Function\FunctionOverloadHandlerInterface;
use Cel\Syntax\Member\CallExpression;
use Cel\Util\ArgumentsUtil;
use Cel\Value\StringValue;
use Cel\Value\TimestampValue;
use Cel\Value\Value;
use Override;
use Psl\DateTime\FormatPattern;
use Psl\DateTime\Timestamp;
use Psl\DateTime\Timezone;
use Psl\Exception\ExceptionInterface;
use Psl\Regex;
use Psl\Str;

final readonly class FromStringHandler implements FunctionOverloadHandlerInterface
{
    private const string RFC3339_PATTERN = '/^(\d{4}-\d{2}-\d{2}T\d{2}:\d{2}:\d{2})(?:\.(\d+))?(.*)$/';

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
        $timestampString = $value->value;

        try {
            /** @var null|array{0: string, 1: string, 2?: string, 3?: string} $parts */
            $parts = Regex\first_match($timestampString, self::RFC3339_PATTERN);
            if (null === $parts) {
                throw new TypeConversionException(
                    Str\format('Failed to parse timestamp string "%s".', $timestampString),
                    $call->getSpan(),
                );
            }

            $mainPart = $parts[1] . ($parts[3] ?? '');
            $fractionalPart = $parts[2] ?? '0';

            // Parse the main part of the timestamp (without fractional seconds).
            $baseTimestamp = Timestamp::parse($mainPart, FormatPattern::Rfc3339WithoutMicroseconds, Timezone::UTC);

            // Normalize the fractional part to nanoseconds.
            $nanosStr = Str\pad_right($fractionalPart, 9, '0');
            $nanosStr = Str\slice($nanosStr, 0, 9);
            $nanoseconds = Str\to_int($nanosStr);

            // Combine the base timestamp with the parsed nanoseconds.
            $finalTimestamp = Timestamp::fromParts($baseTimestamp->getSeconds(), $nanoseconds ?? 0);

            return new TimestampValue($finalTimestamp);
        } catch (ExceptionInterface) {
            try {
                $message = Str\format('Failed to parse timestamp string "%s".', $timestampString);
            } catch (ExceptionInterface) {
                $message = 'Failed to parse timestamp string.';
            }

            throw new TypeConversionException($message, $call->getSpan());
        }
    }
}
