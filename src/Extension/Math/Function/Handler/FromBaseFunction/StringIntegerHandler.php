<?php

declare(strict_types=1);

namespace Cel\Extension\Math\Function\Handler\FromBaseFunction;

use Cel\Exception\EvaluationException;
use Cel\Exception\InternalException;
use Cel\Function\FunctionOverloadHandlerInterface;
use Cel\Syntax\Member\CallExpression;
use Cel\Util\ArgumentsUtil;
use Cel\Value\IntegerValue;
use Cel\Value\StringValue;
use Cel\Value\Value;
use Override;
use Psl\Math;
use Psl\Str;

final readonly class StringIntegerHandler implements FunctionOverloadHandlerInterface
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
        $number = ArgumentsUtil::get($arguments, 0, StringValue::class);
        $fromBase = ArgumentsUtil::get($arguments, 1, IntegerValue::class);

        if ('' === $number->value) {
            throw new EvaluationException(Str\format('fromBase: cannot convert empty string'), $call->getSpan());
        }

        if ($fromBase->value > 36 || $fromBase->value < 2) {
            throw new EvaluationException(
                Str\format('fromBase: base %d is not in the range 2-36', $fromBase->value),
                $call->getSpan(),
            );
        }

        try {
            return new IntegerValue(Math\from_base($number->value, $fromBase->value));
        } catch (Math\Exception\ExceptionInterface $e) {
            throw new EvaluationException($e->getMessage(), $call->getSpan(), $e);
        }
    }
}
