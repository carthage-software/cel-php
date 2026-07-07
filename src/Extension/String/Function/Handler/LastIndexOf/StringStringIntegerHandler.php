<?php

declare(strict_types=1);

namespace Cel\Extension\String\Function\Handler\LastIndexOf;

use Cel\Exception\EvaluationException;
use Cel\Exception\InternalException;
use Cel\Exception\OutOfRangeException;
use Cel\Function\FunctionOverloadHandlerInterface;
use Cel\Syntax\Member\CallExpression;
use Cel\Util\ArgumentsUtil;
use Cel\Util\SearchOffset;
use Cel\Value\IntegerValue;
use Cel\Value\StringValue;
use Cel\Value\Value;
use Override;

use function mb_strlen;
use function mb_strrpos;
use function sprintf;

/**
 * @internal
 */
final readonly class StringStringIntegerHandler implements FunctionOverloadHandlerInterface
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
        $haystack = ArgumentsUtil::get($arguments, 0, StringValue::class);
        $needle = ArgumentsUtil::get($arguments, 1, StringValue::class);
        $offset = ArgumentsUtil::get($arguments, 2, IntegerValue::class);

        if ('' === $needle->value) {
            return new IntegerValue($offset->value);
        }

        try {
            $normalized = SearchOffset::normalize($offset->value, mb_strlen($haystack->value));
            $pos = mb_strrpos($haystack->value, $needle->value, $normalized);

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
