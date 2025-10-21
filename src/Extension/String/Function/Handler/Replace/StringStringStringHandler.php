<?php

declare(strict_types=1);

namespace Cel\Extension\String\Function\Handler\Replace;

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

final readonly class StringStringStringHandler implements FunctionOverloadHandlerInterface
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
        $haystack = ArgumentsUtil::get($arguments, 0, StringValue::class);
        $needle = ArgumentsUtil::get($arguments, 1, StringValue::class);
        $replacement = ArgumentsUtil::get($arguments, 2, StringValue::class);

        try {
            if ('' === $needle->value) {
                // If the needle is an empty string, we insert the replacement between every character.
                $result = Str\join(Str\chunk($haystack->value), $replacement->value) . $replacement->value;
                if ('' !== $haystack->value) {
                    $result = $replacement->value . $result;
                }

                return new StringValue($result);
            }

            return new StringValue(Str\replace($haystack->value, $needle->value, $replacement->value));
        } catch (InvariantViolationException $e) {
            throw new EvaluationException(
                Str\format('String operation failed: %s', $e->getMessage()),
                $call->getSpan(),
                $e,
            );
        }
    }
}
