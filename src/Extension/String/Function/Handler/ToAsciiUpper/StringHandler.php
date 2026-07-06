<?php

declare(strict_types=1);

namespace Cel\Extension\String\Function\Handler\ToAsciiUpper;

use Cel\Exception\EvaluationException;
use Cel\Exception\InternalException;
use Cel\Function\FunctionOverloadHandlerInterface;
use Cel\Syntax\Member\CallExpression;
use Cel\Util\ArgumentsUtil;
use Cel\Value\StringValue;
use Cel\Value\Value;
use Override;

use function mb_ord;
use function mb_str_split;
use function mb_strtoupper;

final readonly class StringHandler implements FunctionOverloadHandlerInterface
{
    /**
     * @param CallExpression $call The call expression.
     * @param list<Value> $arguments The function arguments.
     *
     * @return StringValue The resulting value.
     *
     * @throws InternalException If an internal error occurs.
     * @throws EvaluationException If the string operation fails.
     */
    #[Override]
    public function __invoke(CallExpression $call, array $arguments): StringValue
    {
        $target = ArgumentsUtil::get($arguments, 0, StringValue::class);

        $result = '';
        foreach (mb_str_split($target->value) as $char) {
            $ord = mb_ord($char);
            // a = 97, z = 122
            $result .= $ord >= 97 && $ord <= 122 ? mb_strtoupper($char) : $char;
        }

        return new StringValue($result);
    }
}
