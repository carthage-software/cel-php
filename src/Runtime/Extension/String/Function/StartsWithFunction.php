<?php

declare(strict_types=1);

namespace Cel\Runtime\Extension\String\Function;

use Cel\Runtime\Function\FunctionInterface;
use Cel\Runtime\Value\BooleanValue;
use Cel\Runtime\Value\BytesValue;
use Cel\Runtime\Value\StringValue;
use Cel\Runtime\Value\Value;
use Cel\Runtime\Value\ValueKind;
use Cel\Syntax\Member\CallExpression;
use Override;
use Psl\Str;
use Psl\Str\Byte;

/**
 * @mago-expect analysis:unused-parameter
 */
final readonly class StartsWithFunction implements FunctionInterface
{
    #[Override]
    public function getName(): string
    {
        return 'startsWith';
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
        yield [ValueKind::String, ValueKind::String] =>
            /**
             * @param CallExpression $call      The call expression representing the function call.
             * @param list<Value>    $arguments The arguments passed to the function.
             */
            static function (CallExpression $call, array $arguments): BooleanValue {
                /** @var StringValue $target */
                $target = $arguments[0];
                /** @var StringValue $prefix */
                $prefix = $arguments[1];

                if ($prefix->value === '') {
                    return new BooleanValue(true);
                }

                return new BooleanValue(Str\starts_with($target->value, $prefix->value));
            };

        yield [ValueKind::Bytes, ValueKind::Bytes] =>
            /**
             * @param CallExpression $call      The call expression representing the function call.
             * @param list<Value>    $arguments The arguments passed to the function.
             */
            static function (CallExpression $call, array $arguments): BooleanValue {
                /** @var BytesValue $target */
                $target = $arguments[0];
                /** @var BytesValue $prefix */
                $prefix = $arguments[1];

                if ($prefix->value === '') {
                    return new BooleanValue(true);
                }

                return new BooleanValue(Byte\starts_with($target->value, $prefix->value));
            };
    }
}
