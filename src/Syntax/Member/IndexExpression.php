<?php

declare(strict_types=1);

namespace Cel\Syntax\Member;

use Cel\Span\Span;
use Cel\Syntax\Expression;
use Cel\Syntax\ExpressionKind;
use Override;

/**
 * Represents an index access expression, e.g., `my_list[0]`.
 */
final readonly class IndexExpression extends Expression
{
    public function __construct(
        /** The expression being indexed (e.g., the list or map). */
        public Expression $operand,
        /** The span of the opening bracket `[`. */
        public Span $openingBracket,
        /** The expression used as the index. */
        public Expression $index,
        /** The span of the closing bracket `]`. */
        public Span $closingBracket,
    ) {}

    #[Override]
    public function getKind(): ExpressionKind
    {
        return ExpressionKind::Index;
    }

    #[Override]
    public function getChildren(): array
    {
        return [$this->operand, $this->index];
    }

    #[Override]
    public function getSpan(): Span
    {
        return $this->operand->getSpan()->join($this->closingBracket);
    }
}
