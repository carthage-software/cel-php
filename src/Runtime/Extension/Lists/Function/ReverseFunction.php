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
final readonly class ReverseFunction implements FunctionInterface
{
    #[Override]
    public function getName(): string
    {
        return 'reverse';
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

                return new ListValue(Vec\reverse($list->value));
            };
    }
}
