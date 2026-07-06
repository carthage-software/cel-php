<?php

declare(strict_types=1);

namespace Cel\Extension\Math\Function\Handler\ToBaseFunction;

use Cel\Exception\EvaluationException;
use Cel\Exception\InternalException;
use Cel\Function\FunctionOverloadHandlerInterface;
use Cel\Syntax\Member\CallExpression;
use Cel\Util\ArgumentsUtil;
use Cel\Util\NumberBase;
use Cel\Value\IntegerValue;
use Cel\Value\StringValue;
use Cel\Value\Value;
use Override;

use function sprintf;

final readonly class IntegerIntegerHandler implements FunctionOverloadHandlerInterface
{
    /**
     * @param CallExpression $call The call expression.
     * @param list<Value> $arguments The function arguments.
     *
     * @return StringValue The resulting value.
     *
     * @throws EvaluationException
     * @throws InternalException
     */
    #[Override]
    public function __invoke(CallExpression $call, array $arguments): StringValue
    {
        $number = ArgumentsUtil::get($arguments, 0, IntegerValue::class);
        $toBase = ArgumentsUtil::get($arguments, 1, IntegerValue::class);

        if ($number->value < 0) {
            throw new EvaluationException(
                sprintf('toBase: number %d is negative, only non-negative integers are supported', $number->value),
                $call->getSpan(),
            );
        }

        if ($toBase->value > 36 || $toBase->value < 2) {
            throw new EvaluationException(
                sprintf('toBase: base %d is not in the range 2-36', $toBase->value),
                $call->getSpan(),
            );
        }

        return new StringValue(NumberBase::toBase($number->value, $toBase->value));
    }
}
