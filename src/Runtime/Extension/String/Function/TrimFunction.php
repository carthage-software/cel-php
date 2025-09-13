<?php

declare(strict_types=1);

namespace Cel\Runtime\Extension\String\Function;

use Cel\Runtime\Function\FunctionInterface;
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
final readonly class TrimFunction implements FunctionInterface
{
    #[Override]
    public function getName(): string
    {
        return 'trim';
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
            static function (CallExpression $call, array $arguments): StringValue {
                /** @var StringValue $target */
                $target = $arguments[0];

                return new StringValue(Str\trim($target->value));
            };

        yield [ValueKind::String, ValueKind::String] =>
            /**
             * @param CallExpression $call      The call expression representing the function call.
             * @param list<Value>    $arguments The arguments passed to the function.
             */
            static function (CallExpression $call, array $arguments): StringValue {
                /** @var StringValue $target */
                $target = $arguments[0];
                /** @var StringValue $characters */
                $characters = $arguments[1];

                return new StringValue(Str\trim($target->value, $characters->value));
            };

        yield [ValueKind::Bytes] =>
            /**
             * @param CallExpression $call      The call expression representing the function call.
             * @param list<Value>    $arguments The arguments passed to the function.
             */
            static function (CallExpression $call, array $arguments): BytesValue {
                /** @var BytesValue $target */
                $target = $arguments[0];

                return new BytesValue(Byte\trim($target->value));
            };

        yield [ValueKind::Bytes, ValueKind::Bytes] =>
            /**
             * @param CallExpression $call      The call expression representing the function call.
             * @param list<Value>    $arguments The arguments passed to the function.
             */
            static function (CallExpression $call, array $arguments): BytesValue {
                /** @var BytesValue $target */
                $target = $arguments[0];
                /** @var BytesValue $characters */
                $characters = $arguments[1];

                return new BytesValue(Byte\trim($target->value, $characters->value));
            };
    }
}
