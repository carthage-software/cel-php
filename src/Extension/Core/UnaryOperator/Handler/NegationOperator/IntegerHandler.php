<?php

declare(strict_types=1);

namespace Cel\Extension\Core\UnaryOperator\Handler\NegationOperator;

use Cel\Exception\InternalException;
use Cel\Operator\UnaryOperatorOverloadHandlerInterface;
use Cel\Span\Span;
use Cel\Util\OperandUtil;
use Cel\Value\IntegerValue;
use Cel\Value\Value;
use Override;

final readonly class IntegerHandler implements UnaryOperatorOverloadHandlerInterface
{
    /**
     * @param Span $span The span of the unary expression being evaluated.
     * @param Value $operand The evaluated operand.
     *
     * @return Value The result of the unary operation.
     *
     * @throws InternalException If operand type assertion fails.
     */
    #[Override]
    public function __invoke(Span $span, Value $operand): Value
    {
        $operand = OperandUtil::assert($operand, IntegerValue::class);

        return new IntegerValue(-$operand->value);
    }
}
