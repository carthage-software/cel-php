<?php

declare(strict_types=1);

namespace Cel\Extension\Core\Function\Handler\UInt;

use Cel\Exception\InternalException;
use Cel\Function\FunctionOverloadHandlerInterface;
use Cel\Span\Span;
use Cel\Util\ArgumentsUtil;
use Cel\Value\BooleanValue;
use Cel\Value\UnsignedIntegerValue;
use Cel\Value\Value;
use Override;

/**
 * Handles uint(boolean) -> unsigned_integer
 */
final readonly class FromBooleanHandler implements FunctionOverloadHandlerInterface
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
        $value = ArgumentsUtil::get($arguments, 0, BooleanValue::class);

        return new UnsignedIntegerValue($value->value ? 1 : 0);
    }
}
