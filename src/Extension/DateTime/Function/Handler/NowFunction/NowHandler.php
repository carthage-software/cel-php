<?php

declare(strict_types=1);

namespace Cel\Extension\DateTime\Function\Handler\NowFunction;

use Cel\Function\FunctionOverloadHandlerInterface;
use Cel\Syntax\Member\CallExpression;
use Cel\Value\TimestampValue;
use Cel\Value\Value;
use Override;
use Psl\DateTime\Timestamp;

final readonly class NowHandler implements FunctionOverloadHandlerInterface
{
    /**
     * @param CallExpression $call The call expression.
     * @param list<Value> $arguments The function arguments.
     *
     * @return Value The resulting value.
     */
    #[Override]
    public function __invoke(CallExpression $call, array $arguments): Value
    {
        return new TimestampValue(Timestamp::now());
    }
}
