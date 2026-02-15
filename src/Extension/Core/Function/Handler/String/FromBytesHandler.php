<?php

declare(strict_types=1);

namespace Cel\Extension\Core\Function\Handler\String;

use Cel\Exception\InternalException;
use Cel\Exception\TypeConversionException;
use Cel\Function\FunctionOverloadHandlerInterface;
use Cel\Span\Span;
use Cel\Util\ArgumentsUtil;
use Cel\Value\BytesValue;
use Cel\Value\StringValue;
use Cel\Value\Value;
use Override;
use Psl\Str;

/**
 * Handles string(bytes) -> string
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
     * @throws TypeConversionException If the bytes contain an invalid UTF-8 sequence.
     */
    #[Override]
    public function __invoke(Span $span, array $arguments): Value
    {
        $value = ArgumentsUtil::get($arguments, 0, BytesValue::class);
        if (!Str\is_utf8($value->value)) {
            throw new TypeConversionException(
                Str\format('Cannot convert bytes "%s" to string: invalid UTF-8 sequence.', $value->value),
                $span,
            );
        }

        // Assuming bytes are valid UTF-8, as per CEL spec for string conversion
        return new StringValue($value->value);
    }
}
