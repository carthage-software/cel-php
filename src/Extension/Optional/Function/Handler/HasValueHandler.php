<?php

declare(strict_types=1);

namespace Cel\Extension\Optional\Function\Handler;

use Cel\Exception\InternalException;
use Cel\Function\FunctionOverloadHandlerInterface;
use Cel\Syntax\Member\CallExpression;
use Cel\Util\ArgumentsUtil;
use Cel\Value\BooleanValue;
use Cel\Value\OptionalValue;
use Cel\Value\Value;
use Override;

/**
 * Handles `optional(T).hasValue() -> bool`, reporting whether the optional holds a value.
 */
final readonly class HasValueHandler implements FunctionOverloadHandlerInterface
{
    /**
     * @param CallExpression $call The call expression.
     * @param list<Value> $arguments The function arguments.
     *
     * @return BooleanValue Whether the optional holds a value.
     *
     * @throws InternalException If argument type assertion fails.
     */
    #[Override]
    public function __invoke(CallExpression $call, array $arguments): BooleanValue
    {
        $optional = ArgumentsUtil::get($arguments, 0, OptionalValue::class);

        return new BooleanValue($optional->hasValue());
    }
}
