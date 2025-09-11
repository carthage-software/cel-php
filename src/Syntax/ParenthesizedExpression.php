<?php

declare(strict_types=1);

namespace Cel\Syntax;

use Cel\Span\Span;
use Override;

final readonly class ParenthesizedExpression extends Expression
{
    public function __construct(
        public Span $leftParenthesis,
        public Expression $expression,
        public Span $rightParenthesis,
    ) {}

    #[Override]
    public function getKind(): ExpressionKind
    {
        return ExpressionKind::Parenthesized;
    }

    /**
     * @inheritDoc
     */
    #[Override]
    public function getChildren(): array
    {
        return [$this->expression];
    }

    /**
     * @inheritDoc
     */
    #[Override]
    public function getSpan(): Span
    {
        return $this->leftParenthesis->join($this->rightParenthesis);
    }
}
