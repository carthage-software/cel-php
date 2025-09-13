<?php

declare(strict_types=1);

namespace Cel\Runtime\Extension\Core\Function;

use Cel\Runtime\Exception\TypeConversionException;
use Cel\Runtime\Function\FunctionInterface;
use Cel\Runtime\Value\BooleanValue;
use Cel\Runtime\Value\BytesValue;
use Cel\Runtime\Value\FloatValue;
use Cel\Runtime\Value\IntegerValue;
use Cel\Runtime\Value\StringValue;
use Cel\Runtime\Value\UnsignedIntegerValue;
use Cel\Runtime\Value\Value;
use Cel\Runtime\Value\ValueKind;
use Cel\Syntax\Member\CallExpression;
use Override;
use Psl\Str;

/**
 * @mago-expect analysis:unused-parameter
 */
final readonly class StringFunction implements FunctionInterface
{
    /**
     * @return non-empty-string
     */
    #[Override]
    public function getName(): string
    {
        return 'string';
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
                /** @var StringValue $value */
                $value = $arguments[0];

                return new StringValue($value->value);
            };

        yield [ValueKind::Integer] =>
            /**
             * @param CallExpression $call      The call expression representing the function call.
             * @param list<Value>    $arguments The arguments passed to the function.
             */
            static function (CallExpression $call, array $arguments): StringValue {
                /** @var IntegerValue $value */
                $value = $arguments[0];

                return new StringValue((string) $value->value);
            };

        yield [ValueKind::UnsignedInteger] =>
            /**
             * @param CallExpression $call      The call expression representing the function call.
             * @param list<Value>    $arguments The arguments passed to the function.
             */
            static function (CallExpression $call, array $arguments): StringValue {
                /** @var UnsignedIntegerValue $value */
                $value = $arguments[0];

                return new StringValue((string) $value->value);
            };

        yield [ValueKind::Float] =>
            /**
             * @param CallExpression $call      The call expression representing the function call.
             * @param list<Value>    $arguments The arguments passed to the function.
             */
            static function (CallExpression $call, array $arguments): StringValue {
                /** @var FloatValue $value */
                $value = $arguments[0];

                return new StringValue(Str\format('%g', $value->value));
            };

        yield [ValueKind::Boolean] =>
            /**
             * @param CallExpression $call      The call expression representing the function call.
             * @param list<Value>    $arguments The arguments passed to the function.
             */
            static function (CallExpression $call, array $arguments): StringValue {
                /** @var BooleanValue $value */
                $value = $arguments[0];

                return new StringValue($value->value ? 'true' : 'false');
            };

        yield [ValueKind::Bytes] =>
            /**
             * @param CallExpression $call      The call expression representing the function call.
             * @param list<Value>    $arguments The arguments passed to the function.
             */
            static function (CallExpression $call, array $arguments): StringValue {
                /** @var BytesValue $value */
                $value = $arguments[0];
                if (!Str\is_utf8($value->value)) {
                    throw new TypeConversionException(
                        Str\format('Cannot convert bytes "%s" to string: invalid UTF-8 sequence.', $value->value),
                        $call->getSpan(),
                    );
                }

                // Assuming bytes are valid UTF-8, as per CEL spec for string conversion
                return new StringValue($value->value);
            };
    }
}
