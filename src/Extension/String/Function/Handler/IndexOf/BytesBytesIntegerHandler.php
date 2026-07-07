<?php

declare(strict_types=1);

namespace Cel\Extension\String\Function\Handler\IndexOf;

use Cel\Exception\EvaluationException;
use Cel\Exception\InternalException;
use Cel\Exception\OutOfRangeException;
use Cel\Function\FunctionOverloadHandlerInterface;
use Cel\Syntax\Member\CallExpression;
use Cel\Util\ArgumentsUtil;
use Cel\Util\SearchOffset;
use Cel\Value\BytesValue;
use Cel\Value\IntegerValue;
use Cel\Value\Value;
use Override;

use function sprintf;
use function strlen;
use function strpos;

/**
 * @internal
 */
final readonly class BytesBytesIntegerHandler implements FunctionOverloadHandlerInterface
{
    /**
     * @param CallExpression $call The call expression.
     * @param list<Value> $arguments The function arguments.
     *
     * @return IntegerValue The resulting value.
     *
     * @throws InternalException If an internal error occurs.
     * @throws EvaluationException If the string operation fails.
     */
    #[Override]
    public function __invoke(CallExpression $call, array $arguments): IntegerValue
    {
        $haystack = ArgumentsUtil::get($arguments, 0, BytesValue::class);
        $needle = ArgumentsUtil::get($arguments, 1, BytesValue::class);
        $offset = ArgumentsUtil::get($arguments, 2, IntegerValue::class);

        if ('' === $needle->value) {
            return new IntegerValue($offset->value);
        }

        try {
            $normalized = SearchOffset::normalize($offset->value, strlen($haystack->value));
            $pos = strpos($haystack->value, $needle->value, $normalized);

            return new IntegerValue(false === $pos ? -1 : $pos);
        } catch (OutOfRangeException $e) {
            throw new EvaluationException(
                sprintf('String operation failed: %s', $e->getMessage()),
                $call->getSpan(),
                $e,
            );
        }
    }
}
