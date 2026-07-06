<?php

declare(strict_types=1);

namespace Cel\Extension\Math\Function\Handler\MeanFunction;

use Cel\Exception\EvaluationException;
use Cel\Exception\InternalException;
use Cel\Function\FunctionOverloadHandlerInterface;
use Cel\Syntax\Member\CallExpression;
use Cel\Util\ArgumentsUtil;
use Cel\Value\FloatValue;
use Cel\Value\IntegerValue;
use Cel\Value\ListValue;
use Cel\Value\Value;
use Override;

use function count;
use function sprintf;

final readonly class ListHandler implements FunctionOverloadHandlerInterface
{
    /**
     * @param CallExpression $call The call expression.
     * @param list<Value> $arguments The function arguments.
     *
     * @return FloatValue The resulting value.
     *
     * @throws EvaluationException
     * @throws InternalException
     */
    #[Override]
    public function __invoke(CallExpression $call, array $arguments): FloatValue
    {
        $list = ArgumentsUtil::get($arguments, 0, ListValue::class);

        /** @var list<int|float> $numbers */
        $numbers = [];
        foreach ($list->value as $item) {
            if (!$item instanceof IntegerValue && !$item instanceof FloatValue) {
                throw new EvaluationException(
                    sprintf('mean() only supports lists of integers and floats, got `%s`', $item->getType()),
                    $call->getSpan(),
                );
            }
            $numbers[] = $item->getRawValue();
        }

        if ([] === $numbers) {
            throw new EvaluationException('mean() requires a non-empty list', $call->getSpan());
        }

        // Accumulate each term divided by the count so the result matches the
        // previous implementation's floating-point behaviour exactly.
        $count = count($numbers);
        $mean = 0.0;
        foreach ($numbers as $number) {
            $mean += (float) $number / $count;
        }

        return new FloatValue($mean);
    }
}
