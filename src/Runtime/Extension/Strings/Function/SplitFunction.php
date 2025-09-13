<?php

declare(strict_types=1);

namespace Cel\Runtime\Extension\Strings\Function;

use Cel\Runtime\Exception\RuntimeException;
use Cel\Runtime\Function\FunctionInterface;
use Cel\Runtime\Value\BytesValue;
use Cel\Runtime\Value\IntegerValue;
use Cel\Runtime\Value\ListValue;
use Cel\Runtime\Value\StringValue;
use Cel\Runtime\Value\Value;
use Cel\Runtime\Value\ValueKind;
use Cel\Syntax\Member\CallExpression;
use Override;
use Psl\Str;
use Psl\Str\Byte;
use Psl\Vec;

/**
 * @mago-expect analysis:unused-parameter
 */
final readonly class SplitFunction implements FunctionInterface
{
    #[Override]
    public function getName(): string
    {
        return 'split';
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
            static function (CallExpression $call, array $arguments): ListValue {
                /** @var StringValue $haystack */
                $haystack = $arguments[0];
                /** @var StringValue $delimiter */
                $delimiter = $arguments[1];

                $parts = Str\split($haystack->value, $delimiter->value);

                return new ListValue(Vec\map($parts, static fn(string $p): StringValue => new StringValue($p)));
            };

        yield [ValueKind::String, ValueKind::String, ValueKind::Integer] =>
            /**
             * @param CallExpression $call      The call expression representing the function call.
             * @param list<Value>    $arguments The arguments passed to the function.
             */
            static function (CallExpression $call, array $arguments): ListValue {
                /** @var StringValue $haystack */
                $haystack = $arguments[0];
                /** @var StringValue $delimiter */
                $delimiter = $arguments[1];
                /** @var IntegerValue $limit */
                $limit = $arguments[2];

                if ($limit->value < 1) {
                    throw new RuntimeException(
                        Str\format(
                            'split: limit %d is less than 1, only positive integers are supported',
                            $limit->value,
                        ),
                        $call->getSpan(),
                    );
                }

                $parts = Str\split($haystack->value, $delimiter->value, $limit->value);

                return new ListValue(Vec\map($parts, static fn(string $p): StringValue => new StringValue($p)));
            };

        yield [ValueKind::Bytes, ValueKind::Bytes] =>
            /**
             * @param CallExpression $call      The call expression representing the function call.
             * @param list<Value>    $arguments The arguments passed to the function.
             */
            static function (CallExpression $call, array $arguments): ListValue {
                /** @var BytesValue $haystack */
                $haystack = $arguments[0];
                /** @var BytesValue $delimiter */
                $delimiter = $arguments[1];

                $parts = Byte\split($haystack->value, $delimiter->value);

                return new ListValue(Vec\map($parts, static fn(string $p): BytesValue => new BytesValue($p)));
            };

        yield [ValueKind::Bytes, ValueKind::Bytes, ValueKind::Integer] =>
            /**
             * @param CallExpression $call      The call expression representing the function call.
             * @param list<Value>    $arguments The arguments passed to the function.
             */
            static function (CallExpression $call, array $arguments): ListValue {
                /** @var BytesValue $haystack */
                $haystack = $arguments[0];
                /** @var BytesValue $delimiter */
                $delimiter = $arguments[1];
                /** @var IntegerValue $limit */
                $limit = $arguments[2];

                if ($limit->value < 1) {
                    throw new RuntimeException(
                        Str\format(
                            'split: limit %d is less than 1, only positive integers are supported',
                            $limit->value,
                        ),
                        $call->getSpan(),
                    );
                }

                $parts = Byte\split($haystack->value, $delimiter->value, $limit->value);

                return new ListValue(Vec\map($parts, static fn(string $p): BytesValue => new BytesValue($p)));
            };
    }
}
