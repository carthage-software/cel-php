<?php

declare(strict_types=1);

namespace Cel\Extension\Core\Function\Handler\String;

use Cel\Exception\InternalException;
use Cel\Function\FunctionOverloadHandlerInterface;
use Cel\Span\Span;
use Cel\Util\ArgumentsUtil;
use Cel\Value\StringValue;
use Cel\Value\UnsignedIntegerValue;
use Cel\Value\Value;
use Override;

/**
 * Handles string(unsigned_integer) -> string
 */
final readonly class FromUnsignedIntegerHandler implements FunctionOverloadHandlerInterface
{
    /**
     * @param Span $span The call expression.
     * @param list<Value> $arguments The function arguments.
     *
     * @return Value The resulting value.
     *
     * @throws InternalException If argument type assertion fails.
     */
    #[Override]
    public function __invoke(Span $span, array $arguments): Value
    {
        $value = ArgumentsUtil::get($arguments, 0, UnsignedIntegerValue::class);

        return new StringValue((string) $value->value);
    }
}
