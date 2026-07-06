<?php

declare(strict_types=1);

namespace Cel\Extension\DateTime\Function\Handler\TimestampFunction;

use Cel\Exception\EvaluationException;
use Cel\Exception\InternalException;
use Cel\Function\FunctionOverloadHandlerInterface;
use Cel\Syntax\Member\CallExpression;
use Cel\Util\ArgumentsUtil;
use Cel\Util\TimestampRange;
use Cel\Value\FloatValue;
use Cel\Value\TimestampValue;
use Cel\Value\Value;
use Override;
use Psl\DateTime;
use Psl\DateTime\Exception\ExceptionInterface;
use Psl\DateTime\Timestamp;

use function is_finite;
use function sprintf;

final readonly class FromFloatHandler implements FunctionOverloadHandlerInterface
{
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
        $seconds = ArgumentsUtil::get($arguments, 0, FloatValue::class);

        if (
            !is_finite($seconds->value)
            || $seconds->value < (float) TimestampRange::MIN_SECONDS
            || $seconds->value >= ((float) TimestampRange::MAX_SECONDS + 1.0)
        ) {
            throw new EvaluationException('Timestamp is outside the valid range', $call->getSpan());
        }

        $wholeSeconds = (int) $seconds->value;
        $nanoseconds = (int) (($seconds->value - $wholeSeconds) * DateTime\NANOSECONDS_PER_SECOND);

        try {
            return new TimestampValue(Timestamp::fromParts($wholeSeconds, $nanoseconds));
        } catch (ExceptionInterface $e) {
            try {
                $message = sprintf('Operation failed: %s', $e->getMessage());
            } catch (ExceptionInterface) {
                $message = 'Operation failed.';
            }

            throw new EvaluationException($message, $call->getSpan(), $e);
        }
    }
}
