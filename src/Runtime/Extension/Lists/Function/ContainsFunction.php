<?php

declare(strict_types=1);

namespace Cel\Runtime\Extension\Lists\Function;

use Cel\Runtime\Function\FunctionInterface;
use Cel\Runtime\Value\BooleanValue;
use Cel\Runtime\Value\ListValue;
use Cel\Runtime\Value\Value;
use Cel\Runtime\Value\ValueKind;
use Cel\Syntax\Member\CallExpression;
use Override;
use Psl\Iter;

/**
 * @mago-expect analysis:unused-parameter
 */
final readonly class ContainsFunction implements FunctionInterface
{
    #[Override]
    public function getName(): string
    {
        return 'contains';
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
        $handler =
            /**
             * @param CallExpression $call      The call expression representing the function call.
             * @param list<Value>    $arguments The arguments passed to the function.
             */
            static function (CallExpression $call, array $arguments): BooleanValue {
                /** @var ListValue $list */
                $list = $arguments[0];
                $element = $arguments[1];

                return new BooleanValue(Iter\any($list->value, static fn(Value $item): bool => $item->isEqual(
                    $element,
                )));
            };

        // Dynamically generate an overload for each possible type in the list.
        foreach (ValueKind::cases() as $kind) {
            yield [ValueKind::List, $kind] => $handler;
        }
    }
}
