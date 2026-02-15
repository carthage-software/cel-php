<?php

declare(strict_types=1);

namespace Cel\Extension\List\Function\Handler\FlattenFunction;

use Cel\Exception\InternalException;
use Cel\Function\FunctionOverloadHandlerInterface;
use Cel\Span\Span;
use Cel\Util\ArgumentsUtil;
use Cel\Value\ListValue;
use Cel\Value\Value;
use Override;

final readonly class FlattenHandler implements FunctionOverloadHandlerInterface
{
    /**
     * @param Span $span The call expression.
     * @param list<Value> $arguments The function arguments.
     *
     * @return Value The resulting value.
     *
     * @throws InternalException
     */
    #[Override]
    public function __invoke(Span $span, array $arguments): Value
    {
        $list = ArgumentsUtil::get($arguments, 0, ListValue::class);

        $flattened = [];
        foreach ($list->value as $item) {
            if ($item instanceof ListValue) {
                foreach ($item->value as $nested_item) {
                    $flattened[] = $nested_item;
                }
            } else {
                $flattened[] = $item;
            }
        }

        return new ListValue($flattened);
    }
}
