<?php

declare(strict_types=1);

namespace Cel\Runtime\Extension\List\Function;

use Cel\Runtime\Exception\RuntimeException;
use Cel\Runtime\Function\FunctionInterface;
use Cel\Runtime\Value\IntegerValue;
use Cel\Runtime\Value\ListValue;
use Cel\Runtime\Value\Value;
use Cel\Runtime\Value\ValueKind;
use Cel\Syntax\Member\CallExpression;
use Override;
use Psl\Vec;

final readonly class ChunkFunction implements FunctionInterface
{
    #[Override]
    public function getName(): string
    {
        return 'chunk';
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
        yield [ValueKind::List, ValueKind::Integer] =>
            /**
             * @param CallExpression $call      The call expression representing the function call.
             * @param list<Value>    $arguments The arguments passed to the function.
             */
            static function (CallExpression $call, array $arguments): ListValue {
                /** @var ListValue $list */
                $list = $arguments[0];
                /** @var IntegerValue $size */
                $size = $arguments[1];

                if ($size->value <= 0) {
                    throw new RuntimeException('Chunk size must be a positive integer', $call->getSpan());
                }

                $chunks = Vec\chunk($list->value, $size->value);

                return new ListValue(Vec\map($chunks, static fn(array $chunk): ListValue => new ListValue($chunk)));
            };
    }
}
