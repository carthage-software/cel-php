<?php

declare(strict_types=1);

namespace Cel\Extension\Core\Function\Handler\UInt;

use Cel\Exception\InternalException;
use Cel\Exception\OverflowException;
use Cel\Function\FunctionOverloadHandlerInterface;
use Cel\Span\Span;
use Cel\Util\ArgumentsUtil;
use Cel\Value\FloatValue;
use Cel\Value\UnsignedIntegerValue;
use Cel\Value\Value;
use Override;
use Psl\Math;
use Psl\Str;

/**
 * Handles uint(float) -> unsigned_integer
 */
final readonly class FromFloatHandler implements FunctionOverloadHandlerInterface
{
    /**
     * @param Span $span The call expression.
     * @param list<Value> $arguments The function arguments.
     *
     * @return Value The resulting value.
     *
     * @throws InternalException If argument type assertion fails.
     * @throws OverflowException If the float value is negative, infinity, NaN, or exceeds the maximum unsigned integer value.
     */
    #[Override]
    public function __invoke(Span $span, array $arguments): Value
    {
        $value = ArgumentsUtil::get($arguments, 0, FloatValue::class);
        $floatValue = $value->value;

        if (
            $floatValue < 0.0
            || Math\INFINITY === $floatValue
            || Math\NAN === $floatValue
            || $floatValue > (float) Math\INT64_MAX
        ) {
            throw new OverflowException(Str\format('Float value %f overflows unsigned integer', $floatValue), $span);
        }

        return new UnsignedIntegerValue((int) $floatValue);
    }
}
