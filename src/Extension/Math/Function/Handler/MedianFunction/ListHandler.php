<?php

declare(strict_types=1);

namespace Cel\Extension\Math\Function\Handler\MedianFunction;

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
use Psl\Math;
use Psl\Str;

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
                    Str\format('median() only supports lists of integers and floats, got `%s`', $item->getType()),
                    $call->getSpan(),
                );
            }

            $numbers[] = $item->getRawValue();
        }

        $result = Math\median($numbers);
        if (null === $result) {
            throw new EvaluationException('median() requires a non-empty list', $call->getSpan());
        }

        return new FloatValue($result);
    }
}
