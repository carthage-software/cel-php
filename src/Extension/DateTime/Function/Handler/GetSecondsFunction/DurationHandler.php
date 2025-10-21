<?php

declare(strict_types=1);

namespace Cel\Extension\DateTime\Function\Handler\GetSecondsFunction;

use Cel\Exception\InternalException;
use Cel\Function\FunctionOverloadHandlerInterface;
use Cel\Syntax\Member\CallExpression;
use Cel\Util\ArgumentsUtil;
use Cel\Value\DurationValue;
use Cel\Value\IntegerValue;
use Cel\Value\Value;
use Override;

final readonly class DurationHandler implements FunctionOverloadHandlerInterface
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
        $duration = ArgumentsUtil::get($arguments, 0, DurationValue::class);

        return new IntegerValue((int) $duration->value->getTotalSeconds());
    }
}
