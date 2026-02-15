<?php

declare(strict_types=1);

namespace Cel\Extension\String\Function\Handler\StartsWith;

use Cel\Exception\EvaluationException;
use Cel\Exception\InternalException;
use Cel\Function\FunctionOverloadHandlerInterface;
use Cel\Span\Span;
use Cel\Util\ArgumentsUtil;
use Cel\Value\BooleanValue;
use Cel\Value\BytesValue;
use Cel\Value\Value;
use Override;
use Psl\Exception\InvariantViolationException;
use Psl\Str;
use Psl\Str\Byte;

final readonly class BytesBytesHandler implements FunctionOverloadHandlerInterface
{
    /**
     * @param Span $span The call expression.
     * @param list<Value> $arguments The function arguments.
     *
     * @return BooleanValue The resulting value.
     *
     * @throws InternalException If an internal error occurs.
     * @throws EvaluationException If the string operation fails.
     */
    #[Override]
    public function __invoke(Span $span, array $arguments): BooleanValue
    {
        $target = ArgumentsUtil::get($arguments, 0, BytesValue::class);
        $prefix = ArgumentsUtil::get($arguments, 1, BytesValue::class);

        if ('' === $prefix->value) {
            return new BooleanValue(true);
        }

        try {
            return new BooleanValue(Byte\starts_with($target->value, $prefix->value));
        } catch (InvariantViolationException $e) {
            throw new EvaluationException(Str\format('String operation failed: %s', $e->getMessage()), $span, $e);
        }
    }
}
