<?php

declare(strict_types=1);

namespace Cel\Extension\Core\Function\Handler\Float;

use Cel\Exception\InternalException;
use Cel\Exception\TypeConversionException;
use Cel\Function\FunctionOverloadHandlerInterface;
use Cel\Span\Span;
use Cel\Util\ArgumentsUtil;
use Cel\Value\FloatValue;
use Cel\Value\StringValue;
use Cel\Value\Value;
use Override;
use Psl\Str;
use Psl\Type;

/**
 * Handles float(string) -> float
 */
final readonly class FromStringHandler implements FunctionOverloadHandlerInterface
{
    /**
     * @param Span $span The call expression.
     * @param list<Value> $arguments The function arguments.
     *
     * @return Value The resulting value.
     *
     * @throws InternalException If argument type assertion fails.
     * @throws TypeConversionException If the string cannot be converted to a float.
     */
    #[Override]
    public function __invoke(Span $span, array $arguments): Value
    {
        $value = ArgumentsUtil::get($arguments, 0, StringValue::class);

        try {
            $float = Type\float()->coerce($value->value);
        } catch (Type\Exception\CoercionException) {
            throw new TypeConversionException(Str\format('Cannot convert string "%s" to float.', $value->value), $span);
        }

        return new FloatValue($float);
    }
}
