<?php

declare(strict_types=1);

namespace Cel\Extension\Math\Function\Handler\ToBaseFunction;

use Cel\Exception\EvaluationException;
use Cel\Exception\InternalException;
use Cel\Function\FunctionOverloadHandlerInterface;
use Cel\Span\Span;
use Cel\Util\ArgumentsUtil;
use Cel\Value\IntegerValue;
use Cel\Value\StringValue;
use Cel\Value\Value;
use Override;
use Psl\Math;
use Psl\Str;

final readonly class IntegerIntegerHandler implements FunctionOverloadHandlerInterface
{
    /**
     * @param Span $span The call expression.
     * @param list<Value> $arguments The function arguments.
     *
     * @return StringValue The resulting value.
     *
     * @throws EvaluationException
     * @throws InternalException
     */
    #[Override]
    public function __invoke(Span $span, array $arguments): StringValue
    {
        $number = ArgumentsUtil::get($arguments, 0, IntegerValue::class);
        $toBase = ArgumentsUtil::get($arguments, 1, IntegerValue::class);

        if ($number->value < 0) {
            throw new EvaluationException(
                Str\format('toBase: number %d is negative, only non-negative integers are supported', $number->value),
                $span,
            );
        }

        if ($toBase->value > 36 || $toBase->value < 2) {
            throw new EvaluationException(
                Str\format('toBase: base %d is not in the range 2-36', $toBase->value),
                $span,
            );
        }

        try {
            return new StringValue(Math\to_base($number->value, $toBase->value));
        } catch (Math\Exception\ExceptionInterface $e) {
            throw new EvaluationException($e->getMessage(), $span, $e);
        }
    }
}
