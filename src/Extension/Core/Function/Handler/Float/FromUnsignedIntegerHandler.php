<?php

declare(strict_types=1);

namespace Cel\Extension\Core\Function\Handler\Float;

use Cel\Exception\InternalException;
use Cel\Function\FunctionOverloadHandlerInterface;
use Cel\Syntax\Member\CallExpression;
use Cel\Util\ArgumentsUtil;
use Cel\Value\FloatValue;
use Cel\Value\UnsignedIntegerValue;
use Cel\Value\Value;
use Override;

/**
 * Handles float(uint) -> float
 */
final readonly class FromUnsignedIntegerHandler implements FunctionOverloadHandlerInterface
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
        $value = ArgumentsUtil::get($arguments, 0, UnsignedIntegerValue::class);

        return new FloatValue((float) $value->value);
    }
}
