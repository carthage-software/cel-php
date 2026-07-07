<?php

declare(strict_types=1);

namespace Cel\Extension\Optional\Function\Handler;

use Cel\Function\FunctionOverloadHandlerInterface;
use Cel\Syntax\Member\CallExpression;
use Cel\Value\OptionalValue;
use Cel\Value\Value;
use Override;

/**
 * Handles `optional.none() -> optional(T)`, producing an empty optional.
 *
 * @internal
 */
final readonly class NoneHandler implements FunctionOverloadHandlerInterface
{
    /**
     * @param CallExpression $call The call expression.
     * @param list<Value> $arguments The function arguments.
     *
     * @return OptionalValue An empty optional.
     */
    #[Override]
    public function __invoke(CallExpression $call, array $arguments): OptionalValue
    {
        return OptionalValue::none();
    }
}
