<?php

declare(strict_types=1);

namespace Cel\Runtime\Extension\Core\BinaryOperator;

use Cel\Runtime\Exception\EvaluationException;
use Cel\Runtime\Operator\BinaryOperatorOverloadInterface;
use Cel\Runtime\Value\IntegerValue;
use Cel\Runtime\Value\UnsignedIntegerValue;
use Cel\Runtime\Value\Value;
use Cel\Runtime\Value\ValueKind;
use Cel\Syntax\Binary\BinaryOperatorKind;
use Cel\Syntax\Expression;
use DivisionByZeroError;
use Override;

use function bcmod;

final readonly class ModuloOperator implements BinaryOperatorOverloadInterface
{
    #[Override]
    public function getOperator(): BinaryOperatorKind
    {
        return BinaryOperatorKind::Modulo;
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
                    return new IntegerValue($left->value % $right->value);
                } catch (DivisionByZeroError $exception) { // @mago-expect analysis:avoid-catching-error
                    throw new EvaluationException(
                        'Failed to evaluate modulo: division by zero',
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
                    return new UnsignedIntegerValue(bcmod((string) $left->value, (string) $right->value));
                } catch (DivisionByZeroError $exception) { // @mago-expect analysis:avoid-catching-error
                    throw new EvaluationException(
                        'Failed to evaluate modulo: division by zero',
                        $leftExpr->getSpan()->join($rightExpr->getSpan()),
                        $exception,
                    );
                }
            };
    }
}
