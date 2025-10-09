<?php

declare(strict_types=1);

namespace Cel\Runtime\Extension\Core\BinaryOperator;

use Cel\Runtime\Exception\EvaluationException;
use Cel\Runtime\Operator\BinaryOperatorOverloadInterface;
use Cel\Runtime\Value\FloatValue;
use Cel\Runtime\Value\IntegerValue;
use Cel\Runtime\Value\UnsignedIntegerValue;
use Cel\Runtime\Value\Value;
use Cel\Runtime\Value\ValueKind;
use Cel\Syntax\Binary\BinaryOperatorKind;
use Cel\Syntax\Expression;
use DivisionByZeroError;
use Override;
use Psl\Math;

use function bcdiv;

final readonly class DivisionOperator implements BinaryOperatorOverloadInterface
{
    #[Override]
    public function getOperator(): BinaryOperatorKind
    {
        return BinaryOperatorKind::Divide;
    }

    #[Override]
    public function getOverloads(): iterable
    {
        yield [ValueKind::Integer, ValueKind::Integer] =>
            /**
             * @param IntegerValue $left
             * @param IntegerValue $right
             */
            static function (Value $left, Value $right, Expression $leftExpr, Expression $rightExpr): Value {
                try {
                    return new IntegerValue(Math\div($left->value, $right->value));
                } catch (Math\Exception\DivisionByZeroException $exception) {
                    throw new EvaluationException(
                        'Failed to evaluate division: division by zero',
                        $leftExpr->getSpan()->join($rightExpr->getSpan()),
                        $exception,
                    );
                }
            };

        yield [ValueKind::UnsignedInteger, ValueKind::UnsignedInteger] =>
            /**
             * @param UnsignedIntegerValue $left
             * @param UnsignedIntegerValue $right
             */
            static function (Value $left, Value $right, Expression $leftExpr, Expression $rightExpr): Value {
                try {
                    return new UnsignedIntegerValue(bcdiv((string) $left->value, (string) $right->value));
                } catch (DivisionByZeroError $exception) { // @mago-expect analysis:avoid-catching-error
                    throw new EvaluationException(
                        'Failed to evaluate division: division by zero',
                        $leftExpr->getSpan()->join($rightExpr->getSpan()),
                        $exception,
                    );
                }
            };

        yield [ValueKind::Float, ValueKind::Float] =>
            /**
             * @param FloatValue $left
             * @param FloatValue $right
             */
            static function (Value $left, Value $right, Expression $leftExpr, Expression $rightExpr): Value {
                try {
                    return new FloatValue($left->value / $right->value);
                } catch (DivisionByZeroError $exception) { // @mago-expect analysis:avoid-catching-error
                    throw new EvaluationException(
                        'Failed to evaluate division: division by zero',
                        $leftExpr->getSpan()->join($rightExpr->getSpan()),
                        $exception,
                    );
                }
            };
    }
}
