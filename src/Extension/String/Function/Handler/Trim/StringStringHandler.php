<?php

declare(strict_types=1);

namespace Cel\Extension\String\Function\Handler\Trim;

use Cel\Exception\EvaluationException;
use Cel\Exception\InternalException;
use Cel\Function\FunctionOverloadHandlerInterface;
use Cel\Syntax\Member\CallExpression;
use Cel\Util\ArgumentsUtil;
use Cel\Value\StringValue;
use Cel\Value\Value;
use Override;

use function trim;

final readonly class StringStringHandler implements FunctionOverloadHandlerInterface
{
    /**
     * @param CallExpression $call The call expression.
     * @param list<Value> $arguments The function arguments.
     *
     * @return StringValue The resulting value.
     *
     * @throws InternalException If an internal error occurs.
     * @throws EvaluationException If the string operation fails.
     */
    #[Override]
    public function __invoke(CallExpression $call, array $arguments): StringValue
    {
        $target = ArgumentsUtil::get($arguments, 0, StringValue::class);
        $characters = ArgumentsUtil::get($arguments, 1, StringValue::class);

        return new StringValue(trim($target->value, $characters->value));
    }
}
