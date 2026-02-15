<?php

declare(strict_types=1);

namespace Cel\Extension\String\Function\Handler\LastIndexOf;

use Cel\Exception\EvaluationException;
use Cel\Exception\InternalException;
use Cel\Function\FunctionOverloadHandlerInterface;
use Cel\Span\Span;
use Cel\Util\ArgumentsUtil;
use Cel\Value\IntegerValue;
use Cel\Value\StringValue;
use Cel\Value\Value;
use Override;
use Psl\Exception\ExceptionInterface;
use Psl\Str;

final readonly class StringStringIntegerHandler implements FunctionOverloadHandlerInterface
{
    /**
     * @param Span $span The call expression.
     * @param list<Value> $arguments The function arguments.
     *
     * @return IntegerValue The resulting value.
     *
     * @throws InternalException If an internal error occurs.
     * @throws EvaluationException If the string operation fails.
     */
    #[Override]
    public function __invoke(Span $span, array $arguments): IntegerValue
    {
        $haystack = ArgumentsUtil::get($arguments, 0, StringValue::class);
        $needle = ArgumentsUtil::get($arguments, 1, StringValue::class);
        $offset = ArgumentsUtil::get($arguments, 2, IntegerValue::class);

        if ('' === $needle->value) {
            return new IntegerValue($offset->value);
        }

        try {
            $pos = Str\search_last($haystack->value, $needle->value, $offset->value);

            return new IntegerValue($pos ?? -1);
        } catch (ExceptionInterface $e) {
            throw new EvaluationException(Str\format('String operation failed: %s', $e->getMessage()), $span, $e);
        }
    }
}
