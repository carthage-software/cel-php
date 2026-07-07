<?php

declare(strict_types=1);

namespace Cel\Extension\Core\Function\Handler\UInt;

use Cel\Exception\InternalException;
use Cel\Exception\OverflowException;
use Cel\Function\FunctionOverloadHandlerInterface;
use Cel\Syntax\Member\CallExpression;
use Cel\Util\ArgumentsUtil;
use Cel\Value\FloatValue;
use Cel\Value\UnsignedIntegerValue;
use Cel\Value\Value;
use Override;

use function sprintf;

use const INF;
use const NAN;
use const PHP_INT_MAX;

/**
 * Handles uint(float) -> unsigned_integer
 *
 * @internal
 */
final readonly class FromFloatHandler implements FunctionOverloadHandlerInterface
{
    /**
     * @param CallExpression $call The call expression.
     * @param list<Value> $arguments The function arguments.
     *
     * @return Value The resulting value.
     *
     * @throws InternalException If argument type assertion fails.
     * @throws OverflowException If the float value is negative, infinity, NaN, or exceeds the maximum unsigned integer value.
     */
    #[Override]
    public function __invoke(CallExpression $call, array $arguments): Value
    {
        $value = ArgumentsUtil::get($arguments, 0, FloatValue::class);
        $floatValue = $value->value;

        if ($floatValue < 0.0 || INF === $floatValue || NAN === $floatValue || $floatValue > (float) PHP_INT_MAX) {
            throw new OverflowException(
                sprintf('Float value %f overflows unsigned integer', $floatValue),
                $call->getSpan(),
            );
        }

        return new UnsignedIntegerValue((int) $floatValue);
    }
}
