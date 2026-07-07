<?php

declare(strict_types=1);

namespace Cel\Extension\String\Function\Handler\Split;

use Cel\Exception\EvaluationException;
use Cel\Exception\InternalException;
use Cel\Function\FunctionOverloadHandlerInterface;
use Cel\Syntax\Member\CallExpression;
use Cel\Util\ArgumentsUtil;
use Cel\Util\StringSplit;
use Cel\Value\BytesValue;
use Cel\Value\IntegerValue;
use Cel\Value\ListValue;
use Cel\Value\Value;
use Override;

use function array_map;
use function explode;
use function sprintf;

/**
 * @internal
 */
final readonly class BytesBytesIntegerHandler implements FunctionOverloadHandlerInterface
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
        $limit = ArgumentsUtil::get($arguments, 2, IntegerValue::class);

        if ($limit->value < 1) {
            throw new EvaluationException(
                sprintf('split: limit %d is less than 1, only positive integers are supported', $limit->value),
                $call->getSpan(),
            );
        }

        $parts = '' === $delimiter->value
            ? StringSplit::characters($haystack->value, $limit->value, true)
            : explode($delimiter->value, $haystack->value, $limit->value);

        return new ListValue(array_map(static fn(string $p): BytesValue => new BytesValue($p), $parts));
    }
}
