<?php

declare(strict_types=1);

namespace Cel\Extension\List\Function\Handler\ContainsFunction;

use Cel\Exception\InternalException;
use Cel\Function\FunctionOverloadHandlerInterface;
use Cel\Syntax\Member\CallExpression;
use Cel\Util\ArgumentsUtil;
use Cel\Value\BooleanValue;
use Cel\Value\ListValue;
use Cel\Value\Value;
use Override;
use Psl\Iter;

final readonly class ContainsHandler implements FunctionOverloadHandlerInterface
{
    /**
     * @param CallExpression $call The call expression.
     * @param list<Value> $arguments The function arguments.
     *
     * @return Value The resulting value.
     *
     * @throws InternalException
     */
    #[Override]
    public function __invoke(CallExpression $call, array $arguments): Value
    {
        $list = ArgumentsUtil::get($arguments, 0, ListValue::class);
        $element = ArgumentsUtil::get($arguments, 1, Value::class);

        return new BooleanValue(Iter\any($list->value, static fn(Value $item): bool => $item->isEqual($element)));
    }
}
