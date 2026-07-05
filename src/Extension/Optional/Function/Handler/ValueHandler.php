<?php

declare(strict_types=1);

namespace Cel\Extension\Optional\Function\Handler;

use Cel\Exception\InternalException;
use Cel\Exception\OptionalDereferenceException;
use Cel\Function\FunctionOverloadHandlerInterface;
use Cel\Syntax\Member\CallExpression;
use Cel\Util\ArgumentsUtil;
use Cel\Value\OptionalValue;
use Cel\Value\Value;
use Override;

/**
 * Handles `optional(T).value() -> T`, returning the contained value or erroring
 * when the optional is empty.
 */
final readonly class ValueHandler implements FunctionOverloadHandlerInterface
{
    /**
     * @param CallExpression $call The call expression.
     * @param list<Value> $arguments The function arguments.
     *
     * @return Value The contained value.
     *
     * @throws InternalException If argument type assertion fails.
     * @throws OptionalDereferenceException If the optional is empty.
     */
    #[Override]
    public function __invoke(CallExpression $call, array $arguments): Value
    {
        $optional = ArgumentsUtil::get($arguments, 0, OptionalValue::class);

        $value = $optional->value;
        if (null === $value) {
            throw new OptionalDereferenceException('optional.none() dereference', $call->getSpan());
        }

        return $value;
    }
}
