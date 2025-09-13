<?php

declare(strict_types=1);

namespace Cel\Runtime\Extension\Strings\Function;

use Cel\Runtime\Function\FunctionInterface;
use Cel\Runtime\Value\BytesValue;
use Cel\Runtime\Value\IntegerValue;
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
final readonly class LastIndexOfFunction implements FunctionInterface
{
    #[Override]
    public function getName(): string
    {
        return 'lastIndexOf';
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
            static function (CallExpression $call, array $arguments): IntegerValue {
                /** @var StringValue $haystack */
                $haystack = $arguments[0];
                /** @var StringValue $needle */
                $needle = $arguments[1];

                if ($needle->value === '') {
                    return new IntegerValue(Str\length($haystack->value));
                }

                $pos = Str\search_last($haystack->value, $needle->value);

                return new IntegerValue($pos ?? -1);
            };

        yield [ValueKind::String, ValueKind::String, ValueKind::Integer] =>
            /**
             * @param CallExpression $call      The call expression representing the function call.
             * @param list<Value>    $arguments The arguments passed to the function.
             */
            static function (CallExpression $call, array $arguments): IntegerValue {
                /** @var StringValue $haystack */
                $haystack = $arguments[0];
                /** @var StringValue $needle */
                $needle = $arguments[1];
                /** @var IntegerValue $offset */
                $offset = $arguments[2];

                if ($needle->value === '') {
                    return new IntegerValue($offset->value);
                }

                $pos = Str\search_last($haystack->value, $needle->value, $offset->value);

                return new IntegerValue($pos ?? -1);
            };

        yield [ValueKind::Bytes, ValueKind::Bytes] =>
            /**
             * @param CallExpression $call      The call expression representing the function call.
             * @param list<Value>    $arguments The arguments passed to the function.
             */
            static function (CallExpression $call, array $arguments): IntegerValue {
                /** @var BytesValue $haystack */
                $haystack = $arguments[0];
                /** @var BytesValue $needle */
                $needle = $arguments[1];

                if ($needle->value === '') {
                    return new IntegerValue(Str\length($haystack->value));
                }

                $pos = Byte\search_last($haystack->value, $needle->value);

                return new IntegerValue($pos ?? -1);
            };

        yield [ValueKind::Bytes, ValueKind::Bytes, ValueKind::Integer] =>
            /**
             * @param CallExpression $call      The call expression representing the function call.
             * @param list<Value>    $arguments The arguments passed to the function.
             */
            static function (CallExpression $call, array $arguments): IntegerValue {
                /** @var BytesValue $haystack */
                $haystack = $arguments[0];
                /** @var BytesValue $needle */
                $needle = $arguments[1];
                /** @var IntegerValue $offset */
                $offset = $arguments[2];

                if ($needle->value === '') {
                    return new IntegerValue($offset->value);
                }

                $pos = Byte\search_last($haystack->value, $needle->value, $offset->value);

                return new IntegerValue($pos ?? -1);
            };
    }
}
