<?php

declare(strict_types=1);

namespace Cel\Extension\Core\BinaryOperator\Handler\SubtractionOperator;

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
 * Handles `timestamp - duration`, shifting the timestamp backward by the duration.
 */
final readonly class TimestampDurationHandler implements BinaryOperatorOverloadHandlerInterface
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
        $left = OperandUtil::assertLeft($left, TimestampValue::class);
        $right = OperandUtil::assertRight($right, DurationValue::class);

        $result = $left->value->minus($right->value);
        if (!TimestampRange::isValidSeconds($result->getSeconds())) {
            throw new EvaluationException(
                'Timestamp is outside the valid range',
                $expression->left->getSpan()->join($expression->right->getSpan()),
            );
        }

        return new TimestampValue($result);
    }
}
