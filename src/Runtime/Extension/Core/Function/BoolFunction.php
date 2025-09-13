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
final readonly class BoolFunction implements FunctionInterface
{
    /**
     * @return non-empty-string
     */
    #[Override]
    public function getName(): string
    {
        return 'bool';
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
        yield [ValueKind::Boolean] =>
            /**
             * @param CallExpression $call      The call expression representing the function call.
             * @param list<Value>    $arguments The arguments passed to the function.
             */
            static function (CallExpression $call, array $arguments): BooleanValue {
                /** @var BooleanValue $value */
                $value = $arguments[0];

                return new BooleanValue($value->value);
            };

        yield [ValueKind::Integer] =>
            /**
             * @param CallExpression $call      The call expression representing the function call.
             * @param list<Value>    $arguments The arguments passed to the function.
             */
            static function (CallExpression $call, array $arguments): BooleanValue {
                /** @var IntegerValue $value */
                $value = $arguments[0];

                return new BooleanValue($value->value !== 0);
            };

        yield [ValueKind::UnsignedInteger] =>
            /**
             * @param CallExpression $call      The call expression representing the function call.
             * @param list<Value>    $arguments The arguments passed to the function.
             */
            static function (CallExpression $call, array $arguments): BooleanValue {
                /** @var UnsignedIntegerValue $value */
                $value = $arguments[0];

                return new BooleanValue($value->value !== '0' && $value->value !== 0);
            };

        yield [ValueKind::Float] =>
            /**
             * @param CallExpression $call      The call expression representing the function call.
             * @param list<Value>    $arguments The arguments passed to the function.
             */
            static function (CallExpression $call, array $arguments): BooleanValue {
                /** @var FloatValue $value */
                $value = $arguments[0];

                return new BooleanValue($value->value !== 0.0);
            };

        yield [ValueKind::String] =>
            /**
             * @param CallExpression $call      The call expression representing the function call.
             * @param list<Value>    $arguments The arguments passed to the function.
             */
            static function (CallExpression $call, array $arguments): BooleanValue {
                /** @var StringValue $value */
                $value = $arguments[0];
                $lowerValue = Str\lowercase($value->value);

                if ($lowerValue === 'true') {
                    return new BooleanValue(true);
                }

                if ($lowerValue === 'false') {
                    return new BooleanValue(false);
                }

                throw new TypeConversionException(
                    Str\format('Cannot convert string "%s" to boolean.', $value->value),
                    $call->getSpan(),
                );
            };

        yield [ValueKind::Bytes] =>
            /**
             * @param CallExpression $call      The call expression representing the function call.
             * @param list<Value>    $arguments The arguments passed to the function.
             */
            static function (CallExpression $call, array $arguments): BooleanValue {
                /** @var BytesValue $value */
                $value = $arguments[0];
                $lowerValue = Str\Byte\lowercase($value->value);

                if ($lowerValue === 'true') {
                    return new BooleanValue(true);
                }

                if ($lowerValue === 'false') {
                    return new BooleanValue(false);
                }

                throw new TypeConversionException(
                    Str\format('Cannot convert bytes "%s" to boolean.', $value->value),
                    $call->getSpan(),
                );
            };
    }
}
