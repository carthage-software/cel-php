<?php

declare(strict_types=1);

namespace Cel\Extension\String\Function\Handler\ToAsciiUpper;

use Cel\Exception\EvaluationException;
use Cel\Exception\InternalException;
use Cel\Function\FunctionOverloadHandlerInterface;
use Cel\Syntax\Member\CallExpression;
use Cel\Util\ArgumentsUtil;
use Cel\Value\BytesValue;
use Cel\Value\Value;
use Override;

use function chr;
use function ord;
use function strlen;

/**
 * @internal
 */
final readonly class BytesHandler implements FunctionOverloadHandlerInterface
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
        $target = ArgumentsUtil::get($arguments, 0, BytesValue::class);

        $result = '';
        for ($i = 0; $i < strlen($target->value); ++$i) {
            $byte = $target->value[$i];
            $ord = ord($byte);
            // a = 97, z = 122
            $result .= $ord >= 97 && $ord <= 122 ? chr($ord - 32) : $byte;
        }

        return new BytesValue($result);
    }
}
