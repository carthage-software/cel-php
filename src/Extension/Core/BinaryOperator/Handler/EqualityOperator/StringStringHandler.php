<?php

declare(strict_types=1);

namespace Cel\Extension\Core\BinaryOperator\Handler\EqualityOperator;

use Cel\Exception\InternalException;
use Cel\Exception\UnsupportedOperationException;
use Cel\Operator\BinaryOperatorOverloadHandlerInterface;
use Cel\Span\Span;
use Cel\Util\OperandUtil;
use Cel\Value\BooleanValue;
use Cel\Value\StringValue;
use Cel\Value\Value;
use Override;

final readonly class StringStringHandler implements BinaryOperatorOverloadHandlerInterface
{
    public function __construct(
        private bool $isEqual,
    ) {}

    /**
     * @param Span $span The span of the binary expression.
     * @param Value $left The evaluated left operand.
     * @param Value $right The evaluated right operand.
     *
     * @return Value The result of the binary operation.
     *
     * @throws InternalException If operand type assertion fails.
     * @throws UnsupportedOperationException If the values are not comparable.
     */
    #[Override]
    public function __invoke(Span $span, Value $left, Value $right): Value
    {
        $left = OperandUtil::assertLeft($left, StringValue::class);
        $right = OperandUtil::assertRight($right, StringValue::class);

        return new BooleanValue($this->isEqual ? $left->isEqual($right) : !$left->isEqual($right));
    }
}
