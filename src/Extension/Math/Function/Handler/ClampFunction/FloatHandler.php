<?php

declare(strict_types=1);

namespace Cel\Extension\Math\Function\Handler\ClampFunction;

use Cel\Exception\EvaluationException;
use Cel\Exception\InternalException;
use Cel\Function\FunctionOverloadHandlerInterface;
use Cel\Syntax\Member\CallExpression;
use Cel\Util\ArgumentsUtil;
use Cel\Value\FloatValue;
use Cel\Value\Value;
use Override;
use Psl\Math;

final readonly class FloatHandler implements FunctionOverloadHandlerInterface
{
    /**
     * @param CallExpression $call The call expression.
     * @param list<Value> $arguments The function arguments.
     *
     * @return FloatValue The resulting value.
     *
     * @throws EvaluationException
     * @throws InternalException
     */
    #[Override]
    public function __invoke(CallExpression $call, array $arguments): FloatValue
    {
        $value = ArgumentsUtil::get($arguments, 0, FloatValue::class);
        $min = ArgumentsUtil::get($arguments, 1, FloatValue::class);
        $max = ArgumentsUtil::get($arguments, 2, FloatValue::class);

        try {
            return new FloatValue(Math\clamp($value->value, $min->value, $max->value));
        } catch (Math\Exception\ExceptionInterface $e) {
            throw new EvaluationException($e->getMessage(), $call->getSpan(), $e);
        }
    }
}
