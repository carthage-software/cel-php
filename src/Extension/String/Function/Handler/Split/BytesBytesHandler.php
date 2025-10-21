<?php

declare(strict_types=1);

namespace Cel\Extension\String\Function\Handler\Split;

use Cel\Exception\EvaluationException;
use Cel\Exception\InternalException;
use Cel\Function\FunctionOverloadHandlerInterface;
use Cel\Syntax\Member\CallExpression;
use Cel\Util\ArgumentsUtil;
use Cel\Value\BytesValue;
use Cel\Value\ListValue;
use Cel\Value\Value;
use Override;
use Psl\Exception\InvariantViolationException;
use Psl\Str;
use Psl\Str\Byte;
use Psl\Vec;

final readonly class BytesBytesHandler implements FunctionOverloadHandlerInterface
{
    /**
     * @param CallExpression $call The call expression.
     * @param list<Value> $arguments The function arguments.
     *
     * @return ListValue The resulting value.
     *
     * @throws InternalException If an internal error occurs.
     * @throws EvaluationException If the string operation fails.
     */
    #[Override]
    public function __invoke(CallExpression $call, array $arguments): ListValue
    {
        $haystack = ArgumentsUtil::get($arguments, 0, BytesValue::class);
        $delimiter = ArgumentsUtil::get($arguments, 1, BytesValue::class);

        try {
            $parts = Byte\split($haystack->value, $delimiter->value);

            return new ListValue(Vec\map($parts, static fn(string $p): BytesValue => new BytesValue($p)));
        } catch (InvariantViolationException $e) {
            throw new EvaluationException(
                Str\format('String operation failed: %s', $e->getMessage()),
                $call->getSpan(),
                $e,
            );
        }
    }
}
