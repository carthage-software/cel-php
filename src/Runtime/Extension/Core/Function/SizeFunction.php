<?php

declare(strict_types=1);

namespace Cel\Runtime\Extension\Core\Function;

use Cel\Runtime\Function\FunctionInterface;
use Cel\Runtime\Value\BytesValue;
use Cel\Runtime\Value\IntegerValue;
use Cel\Runtime\Value\ListValue;
use Cel\Runtime\Value\MapValue;
use Cel\Runtime\Value\StringValue;
use Cel\Runtime\Value\Value;
use Cel\Runtime\Value\ValueKind;
use Cel\Syntax\Member\CallExpression;
use Override;
use Psl\Iter;
use Psl\Str;

/**
 * @mago-expect analysis:unused-parameter
 */
final readonly class SizeFunction implements FunctionInterface
{
    /**
     * @return non-empty-string
     */
    #[Override]
    public function getName(): string
    {
        return 'size';
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
        yield [ValueKind::String] =>
            /**
             * @param CallExpression $call      The call expression representing the function call.
             * @param list<Value>    $arguments The arguments passed to the function.
             */
            static function (CallExpression $call, array $arguments): IntegerValue {
                /** @var StringValue $value */
                $value = $arguments[0];

                return new IntegerValue(Str\length($value->value));
            };

        yield [ValueKind::Bytes] =>
            /**
             * @param CallExpression $call      The call expression representing the function call.
             * @param list<Value>    $arguments The arguments passed to the function.
             */
            static function (CallExpression $call, array $arguments): IntegerValue {
                /** @var BytesValue $value */
                $value = $arguments[0];

                return new IntegerValue(Str\Byte\length($value->value));
            };

        yield [ValueKind::List] =>
            /**
             * @param CallExpression $call      The call expression representing the function call.
             * @param list<Value>    $arguments The arguments passed to the function.
             */
            static function (CallExpression $call, array $arguments): IntegerValue {
                /** @var ListValue $value */
                $value = $arguments[0];

                return new IntegerValue(Iter\count($value->value));
            };

        yield [ValueKind::Map] =>
            /**
             * @param CallExpression $call      The call expression representing the function call.
             * @param list<Value>    $arguments The arguments passed to the function.
             */
            static function (CallExpression $call, array $arguments): IntegerValue {
                /** @var MapValue $value */
                $value = $arguments[0];

                return new IntegerValue(Iter\count($value->value));
            };
    }
}
