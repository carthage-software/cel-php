<?php

declare(strict_types=1);

namespace Cel\Extension\List\Function\Handler\ChunkFunction;

use Cel\Exception\EvaluationException;
use Cel\Exception\InternalException;
use Cel\Function\FunctionOverloadHandlerInterface;
use Cel\Span\Span;
use Cel\Util\ArgumentsUtil;
use Cel\Value\IntegerValue;
use Cel\Value\ListValue;
use Cel\Value\Value;
use Override;
use Psl\Vec;

final readonly class ChunkHandler implements FunctionOverloadHandlerInterface
{
    /**
     * @param Span $span The call expression.
     * @param list<Value> $arguments The function arguments.
     *
     * @return Value The resulting value.
     *
     * @throws InternalException If argument type assertion fails.
     * @throws EvaluationException If the chunk size is not a positive integer.
     */
    #[Override]
    public function __invoke(Span $span, array $arguments): Value
    {
        $list = ArgumentsUtil::get($arguments, 0, ListValue::class);
        $size = ArgumentsUtil::get($arguments, 1, IntegerValue::class);

        if ($size->value <= 0) {
            throw new EvaluationException('Chunk size must be a positive integer', $span);
        }

        $chunks = Vec\chunk($list->value, $size->value);

        return new ListValue(Vec\map($chunks, static fn(array $chunk): ListValue => new ListValue($chunk)));
    }
}
