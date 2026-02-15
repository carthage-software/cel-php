<?php

declare(strict_types=1);

namespace Cel\Extension\DateTime\Function\Handler\TimestampFunction;

use Cel\Exception\EvaluationException;
use Cel\Exception\InternalException;
use Cel\Function\FunctionOverloadHandlerInterface;
use Cel\Span\Span;
use Cel\Util\ArgumentsUtil;
use Cel\Value\FloatValue;
use Cel\Value\TimestampValue;
use Cel\Value\Value;
use Override;
use Psl\DateTime;
use Psl\DateTime\Timestamp;
use Psl\Exception\ExceptionInterface;
use Psl\Str;

final readonly class FromFloatHandler implements FunctionOverloadHandlerInterface
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
        $seconds = ArgumentsUtil::get($arguments, 0, FloatValue::class);

        $wholeSeconds = (int) $seconds->value;
        $nanoseconds = (int) (($seconds->value - $wholeSeconds) * DateTime\NANOSECONDS_PER_SECOND);

        try {
            return new TimestampValue(Timestamp::fromParts($wholeSeconds, $nanoseconds));
        } catch (ExceptionInterface $e) {
            try {
                $message = Str\format('Operation failed: %s', $e->getMessage());
            } catch (ExceptionInterface) {
                $message = 'Operation failed.';
            }

            throw new EvaluationException($message, $span, $e);
        }
    }
}
