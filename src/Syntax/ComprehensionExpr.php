<?php

declare(strict_types=1);

namespace Cel\Syntax;

use Override;

/**
 * Represents a comprehension expression.
 * e.g., `[x * x | x, [1, 2, 3], x > 0]`
 */
final readonly class ComprehensionExpr extends AbstractExpr
{
    /**
     * @param string   $iterVar     The name of the iteration variable.
     * @param IdedExpr $iterRange   The expression that provides the range of iteration.
     * @param string   $accuVar     The name of the accumulator variable.
     * @param IdedExpr $accuInit    The initial value of the accumulator.
     * @param IdedExpr $loopCondition The condition that must be met for the loop to continue.
     * @param IdedExpr $loopStep    The expression to execute at the end of each loop.
     * @param IdedExpr $result      The expression that produces the final result.
     */
    public function __construct(
        public string $iterVar,
        public IdedExpr $iterRange,
        public string $accuVar,
        public IdedExpr $accuInit,
        public IdedExpr $loopCondition,
        public IdedExpr $loopStep,
        public IdedExpr $result,
    ) {}

    #[Override]
    public function jsonSerialize(): array
    {
        return [
            'comprehension_expr' => [
                'iter_var' => $this->iterVar,
                'iter_range' => $this->iterRange->jsonSerialize(),
                'accu_var' => $this->accuVar,
                'accu_init' => $this->accuInit->jsonSerialize(),
                'loop_condition' => $this->loopCondition->jsonSerialize(),
                'loop_step' => $this->loopStep->jsonSerialize(),
                'result' => $this->result->jsonSerialize(),
            ],
        ];
    }
}
