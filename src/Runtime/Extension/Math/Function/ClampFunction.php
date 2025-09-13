<?php

declare(strict_types=1);

namespace Cel\Runtime\Extension\Math\Function;

use Cel\Runtime\Function\FunctionInterface;
use Cel\Runtime\Value\FloatValue;
use Cel\Runtime\Value\IntegerValue;
use Cel\Runtime\Value\Value;
use Cel\Runtime\Value\ValueKind;
use Cel\Syntax\Member\CallExpression;
use Override;
use Psl\Math;

/**
 * @mago-expect analysis:unused-parameter
 */
final readonly class ClampFunction implements FunctionInterface
{
    #[Override]
    public function getName(): string
    {
        return 'clamp';
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
        yield [ValueKind::Integer, ValueKind::Integer, ValueKind::Integer] =>
            /**
             * @param CallExpression $call      The call expression representing the function call.
             * @param list<Value>    $arguments The arguments passed to the function.
             */
            static function (CallExpression $call, array $arguments): IntegerValue {
                /** @var IntegerValue $value */
                $value = $arguments[0];
                /** @var IntegerValue $min */
                $min = $arguments[1];
                /** @var IntegerValue $max */
                $max = $arguments[2];

                return new IntegerValue(Math\clamp($value->value, $min->value, $max->value));
            };

        yield [ValueKind::Float, ValueKind::Float, ValueKind::Float] =>
            /**
             * @param CallExpression $call      The call expression representing the function call.
             * @param list<Value>    $arguments The arguments passed to the function.
             */
            static function (CallExpression $call, array $arguments): FloatValue {
                /** @var FloatValue $value */
                $value = $arguments[0];
                /** @var FloatValue $min */
                $min = $arguments[1];
                /** @var FloatValue $max */
                $max = $arguments[2];

                return new FloatValue(Math\clamp($value->value, $min->value, $max->value));
            };
    }
}
