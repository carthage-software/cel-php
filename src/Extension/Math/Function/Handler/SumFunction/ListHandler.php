<?php

declare(strict_types=1);

namespace Cel\Extension\Math\Function\Handler\SumFunction;

use Cel\Exception\EvaluationException;
use Cel\Exception\InternalException;
use Cel\Function\FunctionOverloadHandlerInterface;
use Cel\Syntax\Member\CallExpression;
use Cel\Util\ArgumentsUtil;
use Cel\Value\IntegerValue;
use Cel\Value\ListValue;
use Cel\Value\Value;
use Override;

use function array_map;
use function array_sum;

/**
 * @internal
 */
final readonly class ListHandler implements FunctionOverloadHandlerInterface
{
    /**
     * @param CallExpression $call The call expression.
     * @param list<Value> $arguments The function arguments.
     *
     * @return IntegerValue The resulting value.
     *
     * @throws EvaluationException
     * @throws InternalException
     */
    #[Override]
    public function __invoke(CallExpression $call, array $arguments): IntegerValue
    {
        $list = ArgumentsUtil::get($arguments, 0, ListValue::class);
        if ([] === $list->value) {
            return new IntegerValue(0);
        }

        return new IntegerValue(array_sum(array_map(
            /** @throws EvaluationException If the list contains a non-integer value. */
            static function (Value $v) use ($call): int {
                if ($v instanceof IntegerValue) {
                    return $v->value;
                }

                throw new EvaluationException(
                    'sum() only supports lists of integers, got ' . $v::class,
                    $call->getSpan(),
                );
            },
            $list->value,
        )));
    }
}
