<?php

declare(strict_types=1);

namespace Cel\Extension\Core\UnaryOperator\Handler\NegationOperator;

use Cel\Exception\InternalException;
use Cel\Operator\UnaryOperatorOverloadHandlerInterface;
use Cel\Syntax\Unary\UnaryExpression;
use Cel\Util\OperandUtil;
use Cel\Value\FloatValue;
use Cel\Value\Value;
use Override;

final readonly class FloatHandler implements UnaryOperatorOverloadHandlerInterface
{
    /**
     * @param UnaryExpression $expression The unary expression being evaluated.
     * @param Value $operand The evaluated operand.
     *
     * @return Value The result of the unary operation.
     *
     * @throws InternalException If operand type assertion fails.
     */
    #[Override]
    public function __invoke(UnaryExpression $expression, Value $operand): Value
    {
        $operand = OperandUtil::assert($operand, FloatValue::class);

        return new FloatValue(-$operand->value);
    }
}
