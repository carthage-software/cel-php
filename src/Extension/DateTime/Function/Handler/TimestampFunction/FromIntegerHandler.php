<?php

declare(strict_types=1);

namespace Cel\Extension\DateTime\Function\Handler\TimestampFunction;

use Cel\Exception\EvaluationException;
use Cel\Exception\InternalException;
use Cel\Function\FunctionOverloadHandlerInterface;
use Cel\Syntax\Member\CallExpression;
use Cel\Util\ArgumentsUtil;
use Cel\Util\TimestampRange;
use Cel\Value\IntegerValue;
use Cel\Value\TimestampValue;
use Cel\Value\Value;
use Override;
use Psl\DateTime\Exception\ExceptionInterface;
use Psl\DateTime\Timestamp;

use function sprintf;

/**
 * @internal
 */
final readonly class FromIntegerHandler implements FunctionOverloadHandlerInterface
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
        $seconds = ArgumentsUtil::get($arguments, 0, IntegerValue::class);

        if (!TimestampRange::isValidSeconds($seconds->value)) {
            throw new EvaluationException('Timestamp is outside the valid range', $call->getSpan());
        }

        try {
            return new TimestampValue(Timestamp::fromParts($seconds->value));
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
