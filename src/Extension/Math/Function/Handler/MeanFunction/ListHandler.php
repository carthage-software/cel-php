<?php

declare(strict_types=1);

namespace Cel\Extension\Math\Function\Handler\MeanFunction;

use Cel\Exception\EvaluationException;
use Cel\Exception\InternalException;
use Cel\Function\FunctionOverloadHandlerInterface;
use Cel\Span\Span;
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
     * @param Span $span The call expression.
     * @param list<Value> $arguments The function arguments.
     *
     * @return FloatValue The resulting value.
     *
     * @throws EvaluationException
     * @throws InternalException
     */
    #[Override]
    public function __invoke(Span $span, array $arguments): FloatValue
    {
        $list = ArgumentsUtil::get($arguments, 0, ListValue::class);

        /** @var list<int|float> $numbers */
        $numbers = [];
        foreach ($list->value as $item) {
            if (!$item instanceof IntegerValue && !$item instanceof FloatValue) {
                throw new EvaluationException(
                    Str\format('mean() only supports lists of integers and floats, got `%s`', $item->getType()),
                    $span,
                );
            }
            $numbers[] = $item->getRawValue();
        }

        try {
            $mean = Math\mean($numbers);
            if (null === $mean) {
                throw new EvaluationException('mean() requires a non-empty list', $span);
            }

            return new FloatValue($mean);
        } catch (Math\Exception\ExceptionInterface $e) {
            throw new EvaluationException($e->getMessage(), $span, $e);
        }
    }
}
