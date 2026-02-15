<?php

declare(strict_types=1);

namespace Cel\Extension\Core\Function\Handler\UInt;

use Cel\Exception\InternalException;
use Cel\Exception\TypeConversionException;
use Cel\Function\FunctionOverloadHandlerInterface;
use Cel\Span\Span;
use Cel\Util\ArgumentsUtil;
use Cel\Value\BytesValue;
use Cel\Value\UnsignedIntegerValue;
use Cel\Value\Value;
use Override;
use Psl\Str;

/**
 * Handles uint(bytes) -> unsigned_integer
 */
final readonly class FromBytesHandler implements FunctionOverloadHandlerInterface
{
    /**
     * @param Span $span The call expression.
     * @param list<Value> $arguments The function arguments.
     *
     * @return Value The resulting value.
     *
     * @throws InternalException If argument type assertion fails.
     * @throws TypeConversionException If the bytes cannot be converted to an unsigned integer.
     */
    #[Override]
    public function __invoke(Span $span, array $arguments): Value
    {
        $value = ArgumentsUtil::get($arguments, 0, BytesValue::class);

        try {
            $string = Str\trim_left($value->value, '0');
            $integer = Str\to_int($string);
            if (null === $integer) {
                throw new TypeConversionException(
                    Str\format('Cannot convert bytes "%s" to unsigned integer.', $value->value),
                    $span,
                );
            }

            return new UnsignedIntegerValue($integer);
        } catch (Str\Exception\ExceptionInterface $e) {
            throw new TypeConversionException(
                Str\format('Cannot convert bytes "%s" to unsigned integer: %s', $value->value, $e->getMessage()),
                $span,
                $e,
            );
        }
    }
}
