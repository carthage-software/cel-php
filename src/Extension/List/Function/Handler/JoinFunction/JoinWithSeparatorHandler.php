<?php

declare(strict_types=1);

namespace Cel\Extension\List\Function\Handler\JoinFunction;

use Cel\Exception\EvaluationException;
use Cel\Exception\InternalException;
use Cel\Function\FunctionOverloadHandlerInterface;
use Cel\Span\Span;
use Cel\Util\ArgumentsUtil;
use Cel\Value\ListValue;
use Cel\Value\StringValue;
use Cel\Value\Value;
use Override;
use Psl\Str;

final readonly class JoinWithSeparatorHandler implements FunctionOverloadHandlerInterface
{
    /**
     * @param Span $span The call expression.
     * @param list<Value> $arguments The function arguments.
     *
     * @return Value The resulting value.
     *
     * @throws InternalException If argument type assertion fails.
     * @throws EvaluationException If the list contains non-string values.
     */
    #[Override]
    public function __invoke(Span $span, array $arguments): Value
    {
        $list = ArgumentsUtil::get($arguments, 0, ListValue::class);
        $separator = ArgumentsUtil::get($arguments, 1, StringValue::class);

        $strings = [];
        foreach ($list->value as $item) {
            if (!$item instanceof StringValue) {
                throw new EvaluationException('join: expects a list of strings', $span);
            }

            $strings[] = $item->value;
        }

        return new StringValue(Str\join($strings, $separator->value));
    }
}
