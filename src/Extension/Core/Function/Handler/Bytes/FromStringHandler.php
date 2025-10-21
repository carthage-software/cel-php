<?php

declare(strict_types=1);

namespace Cel\Extension\Core\Function\Handler\Bytes;

use Cel\Exception\InternalException;
use Cel\Function\FunctionOverloadHandlerInterface;
use Cel\Syntax\Member\CallExpression;
use Cel\Util\ArgumentsUtil;
use Cel\Value\BytesValue;
use Cel\Value\StringValue;
use Cel\Value\Value;
use Override;

/**
 * Handles bytes(string) -> bytes
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
     */
    #[Override]
    public function __invoke(CallExpression $call, array $arguments): Value
    {
        $value = ArgumentsUtil::get($arguments, 0, StringValue::class);

        // Per CEL spec, converting a string to bytes uses its UTF-8 representation.
        // Since PHP strings are byte arrays, this is a direct conversion.
        return new BytesValue($value->value);
    }
}
