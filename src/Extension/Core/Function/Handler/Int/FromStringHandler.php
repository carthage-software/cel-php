<?php

declare(strict_types=1);

namespace Cel\Extension\Core\Function\Handler\Int;

use Cel\Exception\InternalException;
use Cel\Exception\TypeConversionException;
use Cel\Function\FunctionOverloadHandlerInterface;
use Cel\Syntax\Member\CallExpression;
use Cel\Util\ArgumentsUtil;
use Cel\Value\IntegerValue;
use Cel\Value\StringValue;
use Cel\Value\Value;
use Override;
use Psl\Str;

/**
 * Handles int(string) -> integer
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
     * @throws TypeConversionException If the string cannot be converted to an integer.
     */
    #[Override]
    public function __invoke(CallExpression $call, array $arguments): Value
    {
        $value = ArgumentsUtil::get($arguments, 0, StringValue::class);

        try {
            $string = Str\trim_left($value->value, '0');
            $integer = Str\to_int($string);
            if (null === $integer) {
                throw new TypeConversionException(
                    Str\format('Cannot convert string "%s" to integer.', $value->value),
                    $call->getSpan(),
                );
            }

            return new IntegerValue($integer);
        } catch (Str\Exception\ExceptionInterface $e) {
            throw new TypeConversionException(
                Str\format('Cannot convert string "%s" to integer: %s', $value->value, $e->getMessage()),
                $call->getSpan(),
                $e,
            );
        }
    }
}
