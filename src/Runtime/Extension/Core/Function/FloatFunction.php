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
use Psl\Type;

/**
 * @mago-expect analysis:unused-parameter
 */
final readonly class FloatFunction implements FunctionInterface
{
    /**
     * @return non-empty-string
     */
    #[Override]
    public function getName(): string
    {
        return 'float';
    }

    /**
     * @return iterable<list<ValueKind>, (callable(CallExpression, list<Value>): Value)>
     */
    #[Override]
    public function getOverloads(): iterable
    {
        yield [ValueKind::Float] =>
            /**
             * @param CallExpression $call      The call expression representing the function call.
             * @param list<Value>    $arguments The arguments passed to the function.
             */
            static function (CallExpression $call, array $arguments): FloatValue {
                /** @var FloatValue $float */
                $float = $arguments[0];

                return new FloatValue($float->value);
            };

        yield [ValueKind::Integer] =>
            /**
             * @param CallExpression $call      The call expression representing the function call.
             * @param list<Value>    $arguments The arguments passed to the function.
             */
            static function (CallExpression $call, array $arguments): FloatValue {
                /** @var IntegerValue $value */
                $value = $arguments[0];

                return new FloatValue((float) $value->value);
            };

        yield [ValueKind::UnsignedInteger] =>
            /**
             * @param CallExpression $call      The call expression representing the function call.
             * @param list<Value>    $arguments The arguments passed to the function.
             */
            static function (CallExpression $call, array $arguments): FloatValue {
                /** @var UnsignedIntegerValue $value */
                $value = $arguments[0];

                return new FloatValue((float) $value->value);
            };

        yield [ValueKind::Boolean] =>
            /**
             * @param CallExpression $call      The call expression representing the function call.
             * @param list<Value>    $arguments The arguments passed to the function.
             */
            static function (CallExpression $call, array $arguments): FloatValue {
                /** @var BooleanValue $value */
                $value = $arguments[0];

                return new FloatValue($value->value ? 1.0 : 0.0);
            };

        yield [ValueKind::String] =>
            /**
             * @param CallExpression $call      The call expression representing the function call.
             * @param list<Value>    $arguments The arguments passed to the function.
             */
            static function (CallExpression $call, array $arguments): FloatValue {
                /** @var StringValue $value */
                $value = $arguments[0];

                try {
                    $float = Type\float()->coerce($value->value);
                } catch (Type\Exception\CoercionException) {
                    throw new TypeConversionException(
                        Str\format('Cannot convert string "%s" to float.', $value->value),
                        $call->getSpan(),
                    );
                }

                return new FloatValue($float);
            };

        yield [ValueKind::Bytes] =>
            /**
             * @param CallExpression $call      The call expression representing the function call.
             * @param list<Value>    $arguments The arguments passed to the function.
             */
            static function (CallExpression $call, array $arguments): FloatValue {
                /** @var BytesValue $value */
                $value = $arguments[0];

                try {
                    $float = Type\float()->coerce($value->value);
                } catch (Type\Exception\CoercionException) {
                    throw new TypeConversionException(
                        Str\format('Cannot convert bytes "%s" to float.', $value->value),
                        $call->getSpan(),
                    );
                }

                return new FloatValue($float);
            };
    }
}
