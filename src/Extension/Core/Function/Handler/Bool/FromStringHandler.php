<?php

declare(strict_types=1);

namespace Cel\Extension\Core\Function\Handler\Bool;

use Cel\Exception\InternalException;
use Cel\Exception\TypeConversionException;
use Cel\Function\FunctionOverloadHandlerInterface;
use Cel\Syntax\Member\CallExpression;
use Cel\Util\ArgumentsUtil;
use Cel\Value\BooleanValue;
use Cel\Value\StringValue;
use Cel\Value\Value;
use Override;
use Psl\Str;

/**
 * Handles bool(string) -> boolean
 */
final readonly class FromStringHandler implements FunctionOverloadHandlerInterface
{
    /**
     * @param CallExpression $call The call expression.
     * @param list<Value> $arguments The function arguments.
     *
     * @return Value The resulting value.
     *
     * @throws InternalException If argument type assertion fails.
     * @throws TypeConversionException If the string cannot be converted to a boolean.
     */
    #[Override]
    public function __invoke(CallExpression $call, array $arguments): Value
    {
        $value = ArgumentsUtil::get($arguments, 0, StringValue::class);
        $lowerValue = Str\lowercase($value->value);

        if ('true' === $lowerValue) {
            return new BooleanValue(true);
        }

        if ('false' === $lowerValue) {
            return new BooleanValue(false);
        }

        throw new TypeConversionException(
            Str\format('Cannot convert string "%s" to boolean.', $value->value),
            $call->getSpan(),
        );
    }
}
