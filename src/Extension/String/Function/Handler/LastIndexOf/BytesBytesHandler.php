<?php

declare(strict_types=1);

namespace Cel\Extension\String\Function\Handler\LastIndexOf;

use Cel\Exception\EvaluationException;
use Cel\Exception\InternalException;
use Cel\Function\FunctionOverloadHandlerInterface;
use Cel\Syntax\Member\CallExpression;
use Cel\Util\ArgumentsUtil;
use Cel\Value\BytesValue;
use Cel\Value\IntegerValue;
use Cel\Value\Value;
use Override;

use function mb_strlen;
use function strrpos;

/**
 * @internal
 */
final readonly class BytesBytesHandler implements FunctionOverloadHandlerInterface
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

        if ('' === $needle->value) {
            return new IntegerValue(mb_strlen($haystack->value));
        }

        $pos = strrpos($haystack->value, $needle->value);

        return new IntegerValue(false === $pos ? -1 : $pos);
    }
}
