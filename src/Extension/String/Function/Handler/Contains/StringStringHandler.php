<?php

declare(strict_types=1);

namespace Cel\Extension\String\Function\Handler\Contains;

use Cel\Exception\EvaluationException;
use Cel\Exception\InternalException;
use Cel\Function\FunctionOverloadHandlerInterface;
use Cel\Syntax\Member\CallExpression;
use Cel\Util\ArgumentsUtil;
use Cel\Value\BooleanValue;
use Cel\Value\StringValue;
use Cel\Value\Value;
use Override;
use Psl\Exception\ExceptionInterface;
use Psl\Str;

final readonly class StringStringHandler implements FunctionOverloadHandlerInterface
{
    /**
     * @param CallExpression $call The call expression.
     * @param list<Value> $arguments The function arguments.
     *
     * @return BooleanValue The resulting value.
     *
     * @throws InternalException If an internal error occurs.
     * @throws EvaluationException If the string operation fails.
     */
    #[Override]
    public function __invoke(CallExpression $call, array $arguments): BooleanValue
    {
        $target = ArgumentsUtil::get($arguments, 0, StringValue::class);
        $substring = ArgumentsUtil::get($arguments, 1, StringValue::class);

        try {
            return new BooleanValue(Str\contains($target->value, $substring->value));
        } catch (ExceptionInterface $e) {
            throw new EvaluationException(
                Str\format('String operation failed: %s', $e->getMessage()),
                $call->getSpan(),
                $e,
            );
        }
    }
}
