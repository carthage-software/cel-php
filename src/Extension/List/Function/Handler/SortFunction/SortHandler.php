<?php

declare(strict_types=1);

namespace Cel\Extension\List\Function\Handler\SortFunction;

use Cel\Exception\InternalException;
use Cel\Function\FunctionOverloadHandlerInterface;
use Cel\Span\Span;
use Cel\Util\ArgumentsUtil;
use Cel\Value\ListValue;
use Cel\Value\Value;
use Override;
use Psl\Vec;

final readonly class SortHandler implements FunctionOverloadHandlerInterface
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

        $sorted_list = Vec\sort($list->value, static function (Value $a, Value $b): int {
            if ($a->isEqual($b)) {
                return 0;
            }

            return $a->isLessThan($b) ? -1 : 1;
        });

        return new ListValue($sorted_list);
    }
}
