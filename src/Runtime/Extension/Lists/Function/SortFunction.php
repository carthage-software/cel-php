<?php

declare(strict_types=1);

namespace Cel\Runtime\Extension\Lists\Function;

use Cel\Runtime\Function\FunctionInterface;
use Cel\Runtime\Value\ListValue;
use Cel\Runtime\Value\Value;
use Cel\Runtime\Value\ValueKind;
use Cel\Syntax\Member\CallExpression;
use Override;
use Psl\Vec;

/**
 * @mago-expect analysis:unused-parameter
 */
final readonly class SortFunction implements FunctionInterface
{
    #[Override]
    public function getName(): string
    {
        return 'sort';
    }

    /**
     * @inheritDoc
     */
    #[Override]
    public function isIdempotent(): bool
    {
        return true;
    }

    /**
     * @return iterable<list<ValueKind>, (callable(CallExpression, list<Value>): Value)>
     */
    #[Override]
    public function getOverloads(): iterable
    {
        yield [ValueKind::List] =>
            /**
             * @param CallExpression $call      The call expression representing the function call.
             * @param list<Value>    $arguments The arguments passed to the function.
             */
            static function (CallExpression $call, array $arguments): ListValue {
                /** @var ListValue $list */
                $list = $arguments[0];

                $sorted_list = Vec\sort($list->value, static function (Value $a, Value $b): int {
                    if ($a->isEqual($b)) {
                        return 0;
                    }

                    return $a->isLessThan($b) ? -1 : 1;
                });

                return new ListValue($sorted_list);
            };
    }
}
