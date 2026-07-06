<?php

declare(strict_types=1);

namespace Cel\Optimizer;

use Cel\Runtime\Runtime;
use Cel\Runtime\RuntimeInterface;
use Cel\Syntax\Aggregate\FieldInitializerNode;
use Cel\Syntax\Aggregate\ListElementNode;
use Cel\Syntax\Aggregate\ListExpression;
use Cel\Syntax\Aggregate\MapEntryNode;
use Cel\Syntax\Aggregate\MapExpression;
use Cel\Syntax\Aggregate\MessageExpression;
use Cel\Syntax\Binary\BinaryExpression;
use Cel\Syntax\ConditionalExpression;
use Cel\Syntax\Expression;
use Cel\Syntax\Member\CallExpression;
use Cel\Syntax\Member\IndexExpression;
use Cel\Syntax\Member\MemberAccessExpression;
use Cel\Syntax\PunctuatedSequence;
use Cel\Syntax\Unary\UnaryExpression;
use Override;

final class Optimizer implements OptimizerInterface
{
    /**
     * @var list<Optimization\OptimizationInterface>
     */
    private array $optimizations;

    /**
     * @param RuntimeInterface $runtime The runtime used by constant folding to evaluate constant
     *                                  sub-expressions, so folding honours the registered extensions.
     * @param null|list<Optimization\OptimizationInterface> $optimizations Optimizations to apply; when
     *                                  null, the default set (constant folding bound to `$runtime`, plus
     *                                  the structural simplifications) is used.
     */
    public function __construct(RuntimeInterface $runtime = new Runtime(), null|array $optimizations = null)
    {
        $this->optimizations = $optimizations ?? [
            new Optimization\ConstantFoldingOptimization($runtime),
            new Optimization\IdentityOperationOptimization(),
            new Optimization\DoubleNegationOptimization(),
            new Optimization\ConditionalSimplificationOptimization(),
            new Optimization\ShortCircuitBooleanOptimization(),
            new Optimization\UnwrapParenthesesOptimization(),
        ];
    }

    #[Override]
    public static function default(): static
    {
        return new self();
    }

    /**
     * @inheritDoc
     */
    #[Override]
    public function addOptimization(Optimization\OptimizationInterface $optimization): void
    {
        $this->optimizations[] = $optimization;
    }

    /**
     * @inheritDoc
     */
    #[Override]
    public function optimize(Expression $expression): Expression
    {
        if ($expression instanceof ListExpression) {
            $optimizedElements = [];
            foreach ($expression->elements->elements as $element) {
                $optimizedElements[] = new ListElementNode($element->question, $this->optimize($element->value));
            }

            $expression = new ListExpression(
                $expression->openingBracket,
                new PunctuatedSequence($optimizedElements, $expression->elements->commas),
                $expression->closingBracket,
            );
        }

        if ($expression instanceof MapExpression) {
            $optimizedEntries = [];
            foreach ($expression->entries->elements as $entry) {
                $optimizedKey = $this->optimize($entry->key);
                $optimizedValue = $this->optimize($entry->value);

                $optimizedEntries[] = new MapEntryNode($entry->question, $optimizedKey, $entry->colon, $optimizedValue);
            }

            $expression = new MapExpression(
                $expression->openingBrace,
                new PunctuatedSequence($optimizedEntries, $expression->entries->commas),
                $expression->closingBrace,
            );
        }

        if ($expression instanceof UnaryExpression) {
            $optimizedOperand = $this->optimize($expression->operand);

            $expression = new UnaryExpression($expression->operator, $optimizedOperand);
        }

        if ($expression instanceof BinaryExpression) {
            $optimizedLeft = $this->optimize($expression->left);
            $optimizedRight = $this->optimize($expression->right);

            $expression = new BinaryExpression($optimizedLeft, $expression->operator, $optimizedRight);
        }

        if ($expression instanceof ConditionalExpression) {
            $optimizedCondition = $this->optimize($expression->condition);
            $optimizedThen = $this->optimize($expression->then);
            $optimizedElse = $this->optimize($expression->else);

            $expression = new ConditionalExpression(
                $optimizedCondition,
                $expression->question,
                $optimizedThen,
                $expression->colon,
                $optimizedElse,
            );
        }

        if ($expression instanceof MemberAccessExpression) {
            $optimizedOperand = $this->optimize($expression->operand);

            $expression = new MemberAccessExpression(
                $optimizedOperand,
                $expression->dot,
                $expression->question,
                $expression->field,
            );
        }

        if ($expression instanceof IndexExpression) {
            $optimizedOperand = $this->optimize($expression->operand);
            $optimizedIndex = $this->optimize($expression->index);

            $expression = new IndexExpression(
                $optimizedOperand,
                $expression->openingBracket,
                $expression->question,
                $optimizedIndex,
                $expression->closingBracket,
            );
        }

        if ($expression instanceof CallExpression) {
            $optimizedTarget = null;
            if (null !== $expression->target) {
                $optimizedTarget = $this->optimize($expression->target);
            }

            $optimizedArguments = [];
            foreach ($expression->arguments->elements as $argument) {
                $optimizedArguments[] = $this->optimize($argument);
            }

            $expression = new CallExpression(
                $optimizedTarget,
                $expression->targetSeparator,
                $expression->function,
                $expression->openingParenthesis,
                new PunctuatedSequence($optimizedArguments, $expression->arguments->commas),
                $expression->closingParenthesis,
            );
        }

        if ($expression instanceof MessageExpression) {
            $initializers = [];
            foreach ($expression->initializers as $initializer) {
                $optimizedInitializerValue = $this->optimize($initializer->value);

                $initializers[] = new FieldInitializerNode(
                    $initializer->question,
                    $initializer->field,
                    $initializer->colon,
                    $optimizedInitializerValue,
                );
            }

            $expression = new MessageExpression(
                $expression->dot,
                $expression->selector,
                $expression->followingSelectors,
                $expression->openingBrace,
                new PunctuatedSequence($initializers, $expression->initializers->commas),
                $expression->closingBrace,
            );
        }

        foreach ($this->optimizations as $optimization) {
            $optimized = $optimization->apply($expression);
            if (null !== $optimized && $optimized !== $expression) { // Guard against no-op optimizations that return the same instance
                return $this->optimize($optimized);
            }
        }

        return $expression;
    }
}
