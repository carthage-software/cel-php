<?php

declare(strict_types=1);

namespace Cel\Extension\Core\Function\Handler\String;

use Cel\Exception\InternalException;
use Cel\Exception\TypeConversionException;
use Cel\Function\FunctionOverloadHandlerInterface;
use Cel\Syntax\Member\CallExpression;
use Cel\Util\ArgumentsUtil;
use Cel\Value\BytesValue;
use Cel\Value\StringValue;
use Cel\Value\Value;
use Override;

use function mb_check_encoding;
use function sprintf;

/**
 * Handles string(bytes) -> string
 *
 * @internal
 */
final readonly class FromBytesHandler implements FunctionOverloadHandlerInterface
{
    /**
     * @param CallExpression $call The call expression.
     * @param list<Value> $arguments The function arguments.
     *
     * @return Value The resulting value.
     *
     * @throws InternalException If argument type assertion fails.
     * @throws TypeConversionException If the bytes contain an invalid UTF-8 sequence.
     */
    #[Override]
    public function __invoke(CallExpression $call, array $arguments): Value
    {
        $value = ArgumentsUtil::get($arguments, 0, BytesValue::class);
        if (!mb_check_encoding($value->value, 'UTF-8')) {
            throw new TypeConversionException(
                sprintf('Cannot convert bytes "%s" to string: invalid UTF-8 sequence.', $value->value),
                $call->getSpan(),
            );
        }

        // Assuming bytes are valid UTF-8, as per CEL spec for string conversion
        return new StringValue($value->value);
    }
}
