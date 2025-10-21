<?php

declare(strict_types=1);

namespace Cel\Extension\Core\Function\Handler\String;

use Cel\Exception\InternalException;
use Cel\Function\FunctionOverloadHandlerInterface;
use Cel\Syntax\Member\CallExpression;
use Cel\Util\ArgumentsUtil;
use Cel\Value\FloatValue;
use Cel\Value\StringValue;
use Cel\Value\Value;
use Override;
use Psl\Str;

/**
 * Handles string(float) -> string
 */
final readonly class FromFloatHandler implements FunctionOverloadHandlerInterface
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
        $value = ArgumentsUtil::get($arguments, 0, FloatValue::class);

        return new StringValue(Str\format('%g', $value->value));
    }
}
