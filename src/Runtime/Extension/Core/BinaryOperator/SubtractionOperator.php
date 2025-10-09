<?php

declare(strict_types=1);

namespace Cel\Runtime\Extension\Core\BinaryOperator;

use Cel\Runtime\Exception\OverflowException;
use Cel\Runtime\Operator\BinaryOperatorOverloadInterface;
use Cel\Runtime\Value\FloatValue;
use Cel\Runtime\Value\IntegerValue;
use Cel\Runtime\Value\UnsignedIntegerValue;
use Cel\Runtime\Value\Value;
use Cel\Runtime\Value\ValueKind;
use Cel\Syntax\Binary\BinaryOperatorKind;
use Cel\Syntax\Expression;
use Override;

use function bccomp;
use function bcsub;

final readonly class SubtractionOperator implements BinaryOperatorOverloadInterface
{
    #[Override]
    public function getOperator(): BinaryOperatorKind
    {
        return BinaryOperatorKind::Minus;
    }

    #[Override]
    public function getOverloads(): iterable
    {
        yield [ValueKind::Integer, ValueKind::Integer] =>
            /**
             * @param IntegerValue $left
             * @param IntegerValue $right
             */
            static fn(Value $left, Value $right): Value => new IntegerValue($left->value - $right->value);

        yield [ValueKind::UnsignedInteger, ValueKind::UnsignedInteger] =>
            /**
             * @param UnsignedIntegerValue $left
             * @param UnsignedIntegerValue $right
             */
            static function (Value $left, Value $right, Expression $leftExpr, Expression $rightExpr): Value {
                $res = bcsub((string) $left->value, (string) $right->value);
                if (bccomp($res, '0') === -1) {
                    throw new OverflowException(
                        'Unsigned integer overflow on subtraction',
                        $leftExpr->getSpan()->join($rightExpr->getSpan()),
                    );
                }
                return new UnsignedIntegerValue($res);
            };

        yield [ValueKind::Float, ValueKind::Float] =>
            /**
             * @param FloatValue $left
             * @param FloatValue $right
             */
            static fn(Value $left, Value $right): Value => new FloatValue($left->value - $right->value);
    }
}
