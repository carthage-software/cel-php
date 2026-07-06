<?php

declare(strict_types=1);

namespace Cel\Extension\Core\BinaryOperator\Handler\AdditionOperator;

use Cel\Exception\EvaluationException;
use Cel\Exception\InternalException;
use Cel\Operator\BinaryOperatorOverloadHandlerInterface;
use Cel\Syntax\Binary\BinaryExpression;
use Cel\Util\OperandUtil;
use Cel\Util\TimestampRange;
use Cel\Value\DurationValue;
use Cel\Value\TimestampValue;
use Cel\Value\Value;
use Override;

/**
 * Handles `duration + timestamp`, shifting the timestamp forward by the duration.
 */
final readonly class DurationTimestampHandler implements BinaryOperatorOverloadHandlerInterface
{
    /**
     * @param BinaryExpression $expression The binary expression being evaluated.
     * @param Value $left The evaluated left operand.
     * @param Value $right The evaluated right operand.
     *
     * @return Value The resulting timestamp.
     *
     * @throws InternalException If operand type assertion fails.
     * @throws EvaluationException If the resulting timestamp is outside the valid range.
     */
    #[Override]
    public function __invoke(BinaryExpression $expression, Value $left, Value $right): Value
    {
        $left = OperandUtil::assertLeft($left, DurationValue::class);
        $right = OperandUtil::assertRight($right, TimestampValue::class);

        $result = $right->value->plus($left->value);
        if (!TimestampRange::isValidSeconds($result->getSeconds())) {
            throw new EvaluationException(
                'Timestamp is outside the valid range',
                $expression->left->getSpan()->join($expression->right->getSpan()),
            );
        }

        return new TimestampValue($result);
    }
}
