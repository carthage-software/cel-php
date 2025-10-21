<?php

declare(strict_types=1);

namespace Cel\Extension\String\Function\Handler\ToAsciiLower;

use Cel\Exception\EvaluationException;
use Cel\Exception\InternalException;
use Cel\Function\FunctionOverloadHandlerInterface;
use Cel\Syntax\Member\CallExpression;
use Cel\Util\ArgumentsUtil;
use Cel\Value\StringValue;
use Cel\Value\Value;
use Override;
use Psl\Exception\InvariantViolationException;
use Psl\Str;

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

        try {
            $result = '';
            foreach (Str\chunk($target->value) as $char) {
                $ord = Str\ord($char);
                // A = 65, Z = 90
                $result .= $ord >= 65 && $ord <= 90 ? Str\lowercase($char) : $char;
            }

            return new StringValue($result);
        } catch (InvariantViolationException $e) {
            throw new EvaluationException(
                Str\format('String operation failed: %s', $e->getMessage()),
                $call->getSpan(),
                $e,
            );
        }
    }
}
