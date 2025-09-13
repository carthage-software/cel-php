<?php

declare(strict_types=1);

namespace Cel\Runtime\Extension\List\Function;

use Cel\Runtime\Exception\RuntimeException;
use Cel\Runtime\Function\FunctionInterface;
use Cel\Runtime\Value\ListValue;
use Cel\Runtime\Value\StringValue;
use Cel\Runtime\Value\Value;
use Cel\Runtime\Value\ValueKind;
use Cel\Syntax\Member\CallExpression;
use Override;
use Psl\Str;

final readonly class JoinFunction implements FunctionInterface
{
    #[Override]
    public function getName(): string
    {
        return 'join';
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
            static function (CallExpression $call, array $arguments): StringValue {
                /** @var ListValue $list */
                $list = $arguments[0];

                $strings = [];
                foreach ($list->value as $item) {
                    if (!$item instanceof StringValue) {
                        throw new RuntimeException('join: expects a list of strings', $call->getSpan());
                    }
                    $strings[] = $item->value;
                }

                return new StringValue(Str\join($strings, ''));
            };

        yield [ValueKind::List, ValueKind::String] =>
            /**
             * @param CallExpression $call      The call expression representing the function call.
             * @param list<Value>    $arguments The arguments passed to the function.
             */
            static function (CallExpression $call, array $arguments): StringValue {
                /** @var ListValue $list */
                $list = $arguments[0];
                /** @var StringValue $separator */
                $separator = $arguments[1];

                $strings = [];
                foreach ($list->value as $item) {
                    if (!$item instanceof StringValue) {
                        throw new RuntimeException('join: expects a list of strings', $call->getSpan());
                    }

                    $strings[] = $item->value;
                }

                return new StringValue(Str\join($strings, $separator->value));
            };
    }
}
