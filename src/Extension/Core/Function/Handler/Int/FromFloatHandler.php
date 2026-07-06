<?php

declare(strict_types=1);

namespace Cel\Extension\Core\Function\Handler\Int;

use Cel\Exception\InternalException;
use Cel\Exception\OverflowException;
use Cel\Function\FunctionOverloadHandlerInterface;
use Cel\Syntax\Member\CallExpression;
use Cel\Util\ArgumentsUtil;
use Cel\Value\FloatValue;
use Cel\Value\IntegerValue;
use Cel\Value\Value;
use Override;
use Psl\Math;
use Psl\Str;

use function is_finite;

/**
 * Handles int(float) -> integer
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
     * @throws OverflowException If the float value exceeds the maximum or minimum integer value.
     */
    #[Override]
    public function __invoke(CallExpression $call, array $arguments): Value
    {
        $value = ArgumentsUtil::get($arguments, 0, FloatValue::class);
        $floatValue = $value->value;

        if (!is_finite($floatValue) || $floatValue >= (float) Math\INT64_MAX || $floatValue <= (float) Math\INT64_MIN) {
            throw new OverflowException(
                Str\format(
                    'Double value %s overflows the integer range',
                    is_finite($floatValue) ? (string) $floatValue : 'NaN or infinity',
                ),
                $call->getSpan(),
            );
        }

        return new IntegerValue((int) $floatValue);
    }
}
