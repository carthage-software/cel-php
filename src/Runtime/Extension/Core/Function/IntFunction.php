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
use Psl\Type;

use function bccomp;

/**
 * @mago-expect analysis:unused-parameter
 */
final readonly class IntFunction implements FunctionInterface
{
    /**
     * @return non-empty-string
     */
    #[Override]
    public function getName(): string
    {
        return 'int';
    }

    /**
     * @return iterable<list<ValueKind>, (callable(CallExpression, list<Value>): Value)>
     */
    #[Override]
    public function getOverloads(): iterable
    {
        yield [ValueKind::Integer] =>
            /**
             * @param CallExpression $call      The call expression representing the function call.
             * @param list<Value>    $arguments The arguments passed to the function.
             */
            static function (CallExpression $call, array $arguments): IntegerValue {
                /** @var IntegerValue $value */
                $value = $arguments[0];

                return new IntegerValue($value->value);
            };

        yield [ValueKind::UnsignedInteger] =>
            /**
             * @param CallExpression $call      The call expression representing the function call.
             * @param list<Value>    $arguments The arguments passed to the function.
             */
            static function (CallExpression $call, array $arguments): IntegerValue {
                /** @var UnsignedIntegerValue $value */
                $value = $arguments[0];
                $uintValue = $value->value;

                if (Type\int()->matches($uintValue)) {
                    return new IntegerValue($uintValue);
                }

                if (bccomp($uintValue, (string) Math\INT64_MAX) === 1) {
                    throw new OverflowException(
                        Str\format(
                            'Unsigned integer value %s overflows maximum integer value %d',
                            $uintValue,
                            Math\INT64_MAX,
                        ),
                        $call->getSpan(),
                    );
                }

                return new IntegerValue((int) $uintValue);
            };

        yield [ValueKind::Float] =>
            /**
             * @param CallExpression $call      The call expression representing the function call.
             * @param list<Value>    $arguments The arguments passed to the function.
             */
            static function (CallExpression $call, array $arguments): IntegerValue {
                /** @var FloatValue $value */
                $value = $arguments[0];
                $floatValue = $value->value;

                if ($floatValue > Math\INT64_MAX || $floatValue < Math\INT64_MIN) {
                    throw new OverflowException(
                        Str\format('Float value %s overflows maximum integer value %d', $floatValue, Math\INT64_MAX),
                        $call->getSpan(),
                    );
                }

                return new IntegerValue((int) $floatValue);
            };

        yield [ValueKind::Boolean] =>
            /**
             * @param CallExpression $call      The call expression representing the function call.
             * @param list<Value>    $arguments The arguments passed to the function.
             */
            static function (CallExpression $call, array $arguments): IntegerValue {
                /** @var BooleanValue $value */
                $value = $arguments[0];

                return new IntegerValue($value->value ? 1 : 0);
            };

        yield [ValueKind::String] =>
            /**
             * @param CallExpression $call      The call expression representing the function call.
             * @param list<Value>    $arguments The arguments passed to the function.
             */
            static function (CallExpression $call, array $arguments): IntegerValue {
                /** @var StringValue $value */
                $value = $arguments[0];

                $string = Str\trim_left($value->value, '0');
                $integer = Str\to_int($string);
                if (null === $integer) {
                    throw new TypeConversionException(
                        Str\format('Cannot convert string "%s" to integer.', $value->value),
                        $call->getSpan(),
                    );
                }

                return new IntegerValue($integer);
            };

        yield [ValueKind::Bytes] =>
            /**
             * @param CallExpression $call      The call expression representing the function call.
             * @param list<Value>    $arguments The arguments passed to the function.
             */
            static function (CallExpression $call, array $arguments): IntegerValue {
                /** @var BytesValue $value */
                $value = $arguments[0];

                $string = Str\trim_left($value->value, '0');
                $integer = Str\to_int($string);
                if (null === $integer) {
                    throw new TypeConversionException(
                        Str\format('Cannot convert bytes "%s" to integer.', $value->value),
                        $call->getSpan(),
                    );
                }

                return new IntegerValue($integer);
            };
    }
}
