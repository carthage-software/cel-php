<?php

declare(strict_types=1);

namespace Cel\Runtime\Extension\Core\Function;

use Cel\Runtime\Exception\OverflowException;
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
use Psl\Math;
use Psl\Str;

/**
 * @mago-expect analysis:unused-parameter
 */
final readonly class UIntFunction implements FunctionInterface
{
    /**
     * @return non-empty-string
     */
    #[Override]
    public function getName(): string
    {
        return 'uint';
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
        yield [ValueKind::UnsignedInteger] =>
            /**
             * @param CallExpression $call      The call expression representing the function call.
             * @param list<Value>    $arguments The arguments passed to the function.
             */
            static function (CallExpression $call, array $arguments): UnsignedIntegerValue {
                /** @var UnsignedIntegerValue $value */
                $value = $arguments[0];

                return new UnsignedIntegerValue($value->value);
            };

        yield [ValueKind::Integer] =>
            /**
             * @param CallExpression $call      The call expression representing the function call.
             * @param list<Value>    $arguments The arguments passed to the function.
             */
            static function (CallExpression $call, array $arguments): UnsignedIntegerValue {
                /** @var IntegerValue $value */
                $value = $arguments[0];
                $intValue = $value->value;

                if ($intValue < 0) {
                    throw new OverflowException(
                        Str\format('Integer value %d overflows unsigned integer', $intValue),
                        $call->getSpan(),
                    );
                }

                return new UnsignedIntegerValue($intValue);
            };

        yield [ValueKind::Float] =>
            /**
             * @param CallExpression $call      The call expression representing the function call.
             * @param list<Value>    $arguments The arguments passed to the function.
             */
            static function (CallExpression $call, array $arguments): UnsignedIntegerValue {
                /** @var FloatValue $value */
                $value = $arguments[0];
                $floatValue = $value->value;

                if (
                    $floatValue < 0.0
                    || Math\INFINITY === $floatValue
                    || Math\NAN === $floatValue
                    || $floatValue > (float) Math\INT64_MAX
                ) {
                    throw new OverflowException(
                        Str\format('Float value %f overflows unsigned integer', $floatValue),
                        $call->getSpan(),
                    );
                }

                return new UnsignedIntegerValue((int) $floatValue);
            };

        yield [ValueKind::Boolean] =>
            /**
             * @param CallExpression $call      The call expression representing the function call.
             * @param list<Value>    $arguments The arguments passed to the function.
             */
            static function (CallExpression $call, array $arguments): UnsignedIntegerValue {
                /** @var BooleanValue $value */
                $value = $arguments[0];

                return new UnsignedIntegerValue($value->value ? 1 : 0);
            };

        yield [ValueKind::String] =>
            /**
             * @param CallExpression $call      The call expression representing the function call.
             * @param list<Value>    $arguments The arguments passed to the function.
             */
            static function (CallExpression $call, array $arguments): UnsignedIntegerValue {
                /** @var StringValue $value */
                $value = $arguments[0];

                $string = Str\trim_left($value->value, '0');
                $integer = Str\to_int($string);
                if (null === $integer) {
                    throw new TypeConversionException(
                        Str\format('Cannot convert string "%s" to unsigned integer.', $value->value),
                        $call->getSpan(),
                    );
                }

                return new UnsignedIntegerValue($integer);
            };

        yield [ValueKind::Bytes] =>
            /**
             * @param CallExpression $call      The call expression representing the function call.
             * @param list<Value>    $arguments The arguments passed to the function.
             */
            static function (CallExpression $call, array $arguments): UnsignedIntegerValue {
                /** @var BytesValue $value */
                $value = $arguments[0];

                $string = Str\trim_left($value->value, '0');
                $integer = Str\to_int($string);
                if (null === $integer) {
                    throw new TypeConversionException(
                        Str\format('Cannot convert bytes "%s" to unsigned integer.', $value->value),
                        $call->getSpan(),
                    );
                }

                return new UnsignedIntegerValue($integer);
            };
    }
}
