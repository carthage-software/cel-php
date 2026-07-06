<?php

declare(strict_types=1);

namespace Cel\Extension\String\Function\Handler\Replace;

use Cel\Exception\EvaluationException;
use Cel\Exception\InternalException;
use Cel\Function\FunctionOverloadHandlerInterface;
use Cel\Syntax\Member\CallExpression;
use Cel\Util\ArgumentsUtil;
use Cel\Value\BytesValue;
use Cel\Value\Value;
use Override;

use function implode;
use function str_replace;
use function str_split;

final readonly class BytesBytesBytesHandler implements FunctionOverloadHandlerInterface
{
    /**
     * @param CallExpression $call The call expression.
     * @param list<Value> $arguments The function arguments.
     *
     * @return BytesValue The resulting value.
     *
     * @throws InternalException If an internal error occurs.
     * @throws EvaluationException If the string operation fails.
     */
    #[Override]
    public function __invoke(CallExpression $call, array $arguments): BytesValue
    {
        $haystack = ArgumentsUtil::get($arguments, 0, BytesValue::class);
        $needle = ArgumentsUtil::get($arguments, 1, BytesValue::class);
        $replacement = ArgumentsUtil::get($arguments, 2, BytesValue::class);

        if ('' === $needle->value) {
            // If the needle is an empty string, we insert the replacement between every character.
            $result = implode($replacement->value, str_split($haystack->value)) . $replacement->value;
            if ('' !== $haystack->value) {
                $result = $replacement->value . $result;
            }

            return new BytesValue($result);
        }

        return new BytesValue(str_replace($needle->value, $replacement->value, $haystack->value));
    }
}
