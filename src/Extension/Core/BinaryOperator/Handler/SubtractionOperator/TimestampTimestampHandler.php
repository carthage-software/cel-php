<?php

declare(strict_types=1);

namespace Cel\Extension\Core\BinaryOperator\Handler\SubtractionOperator;

use Cel\Exception\EvaluationException;
use Cel\Exception\InternalException;
use Cel\Operator\BinaryOperatorOverloadHandlerInterface;
use Cel\Syntax\Binary\BinaryExpression;
use Cel\Util\DurationRange;
use Cel\Util\OperandUtil;
use Cel\Value\DurationValue;
use Cel\Value\TimestampValue;
use Cel\Value\Value;
use Override;
use Psl\DateTime\Duration;

/**
 * Handles `timestamp - timestamp`, yielding the duration between the two instants.
 *
 * @internal
 */
final readonly class TimestampTimestampHandler implements BinaryOperatorOverloadHandlerInterface
{
    /**
     * @param BinaryExpression $expression The binary expression being evaluated.
     * @param Value $left The evaluated left operand.
     * @param Value $right The evaluated right operand.
     *
     * @return Value The resulting duration.
     *
     * @throws InternalException If operand type assertion fails.
     * @throws EvaluationException If the resulting duration is outside the valid range.
     */
    #[Override]
    public function __invoke(BinaryExpression $expression, Value $left, Value $right): Value
    {
        $left = OperandUtil::assertLeft($left, TimestampValue::class);
        $right = OperandUtil::assertRight($right, TimestampValue::class);

        $seconds = $left->value->getSeconds() - $right->value->getSeconds();
        $nanoseconds = $left->value->getNanoseconds() - $right->value->getNanoseconds();
        $result = Duration::fromParts(0, 0, $seconds, $nanoseconds);

        if (!DurationRange::isValid($result)) {
            throw new EvaluationException(
                'Duration is outside the valid range',
                $expression->left->getSpan()->join($expression->right->getSpan()),
            );
        }

        return new DurationValue($result);
    }
}
