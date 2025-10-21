<?php

declare(strict_types=1);

namespace Cel\Extension\Math\Function\Handler\ClampFunction;

use Cel\Exception\EvaluationException;
use Cel\Exception\InternalException;
use Cel\Function\FunctionOverloadHandlerInterface;
use Cel\Syntax\Member\CallExpression;
use Cel\Util\ArgumentsUtil;
use Cel\Value\IntegerValue;
use Cel\Value\Value;
use Override;
use Psl\Math;

final readonly class IntegerHandler implements FunctionOverloadHandlerInterface
{
    /**
     * @param CallExpression $call The call expression.
     * @param list<Value> $arguments The function arguments.
     *
     * @return IntegerValue The resulting value.
     *
     * @throws EvaluationException
     * @throws InternalException
     */
    #[Override]
    public function __invoke(CallExpression $call, array $arguments): IntegerValue
    {
        $value = ArgumentsUtil::get($arguments, 0, IntegerValue::class);
        $min = ArgumentsUtil::get($arguments, 1, IntegerValue::class);
        $max = ArgumentsUtil::get($arguments, 2, IntegerValue::class);

        try {
            return new IntegerValue(Math\clamp($value->value, $min->value, $max->value));
        } catch (Math\Exception\ExceptionInterface $e) {
            throw new EvaluationException($e->getMessage(), $call->getSpan(), $e);
        }
    }
}
