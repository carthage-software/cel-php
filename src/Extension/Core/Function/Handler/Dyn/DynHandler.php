<?php

declare(strict_types=1);

namespace Cel\Extension\Core\Function\Handler\Dyn;

use Cel\Exception\InternalException;
use Cel\Function\FunctionOverloadHandlerInterface;
use Cel\Syntax\Member\CallExpression;
use Cel\Util\ArgumentsUtil;
use Cel\Value\Value;
use Override;

/**
 * Handles dyn(any) -> any
 *
 * `dyn` is the identity function: it returns its argument unchanged. It exists
 * to signal to a type checker that a value should be treated as dynamic; at
 * runtime it has no effect.
 *
 * @internal
 */
final readonly class DynHandler implements FunctionOverloadHandlerInterface
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
        return ArgumentsUtil::get($arguments, 0, Value::class);
    }
}
