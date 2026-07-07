<?php

declare(strict_types=1);

namespace Cel\Extension\DateTime\Function\Handler\GetMillisecondsFunction;

use Cel\Exception\InternalException;
use Cel\Function\FunctionOverloadHandlerInterface;
use Cel\Syntax\Member\CallExpression;
use Cel\Util\ArgumentsUtil;
use Cel\Value\DurationValue;
use Cel\Value\IntegerValue;
use Cel\Value\Value;
use Override;

use function intdiv;

/**
 * @internal
 */
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

        // @mago-expect analysis:unhandled-thrown-type(2) - division by zero and arithmetic error are not possible.
        return new IntegerValue(intdiv($duration->value->getNanoseconds(), 1_000_000));
    }
}
