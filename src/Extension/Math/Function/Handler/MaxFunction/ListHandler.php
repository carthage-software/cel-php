<?php

declare(strict_types=1);

namespace Cel\Extension\Math\Function\Handler\MaxFunction;

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

use function is_int;
use function max;
use function sprintf;

/**
 * @internal
 */
final readonly class ListHandler implements FunctionOverloadHandlerInterface
{
    /**
     * @param CallExpression $call The call expression.
     * @param list<Value> $arguments The function arguments.
     *
     * @return Value The resulting value.
     *
     * @throws EvaluationException
     * @throws InternalException
     */
    #[Override]
    public function __invoke(CallExpression $call, array $arguments): Value
    {
        $list = ArgumentsUtil::get($arguments, 0, ListValue::class);

        /** @var list<int|float> $numbers */
        $numbers = [];
        foreach ($list->value as $item) {
            if (!$item instanceof IntegerValue && !$item instanceof FloatValue) {
                throw new EvaluationException(
                    sprintf('max() only supports lists of integers and floats, got `%s`', $item->getType()),
                    $call->getSpan(),
                );
            }

            $numbers[] = $item->value;
        }

        if ([] === $numbers) {
            throw new EvaluationException('max() requires a non-empty list', $call->getSpan());
        }

        $result = max($numbers);

        if (is_int($result)) {
            return new IntegerValue($result);
        }

        return new FloatValue($result);
    }
}
