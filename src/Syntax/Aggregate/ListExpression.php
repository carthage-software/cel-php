<?php

declare(strict_types=1);

namespace Cel\Syntax\Aggregate;

use Cel\Span\Span;
use Cel\Syntax\Expression;
use Cel\Syntax\ExpressionKind;
use Cel\Syntax\PunctuatedSequence;
use Override;

/**
 * Represents a list literal, e.g., `[1, "foo", true]`.
 */
final readonly class ListExpression extends Expression
{
    /**
     * @param Span                           $openingBracket The span of the opening bracket `[`.
     * @param PunctuatedSequence<Expression> $elements       The list of element expressions.
     * @param Span                           $closingBracket The span of the closing bracket `]`.
     */
    public function __construct(
        public Span $openingBracket,
        public PunctuatedSequence $elements,
        public Span $closingBracket,
    ) {}

    #[Override]
    public function getKind(): ExpressionKind
    {
        return ExpressionKind::List;
    }

    #[Override]
    public function getChildren(): array
    {
        return $this->elements->elements;
    }

    #[Override]
    public function getSpan(): Span
    {
        return $this->openingBracket->join($this->closingBracket);
    }
}
