<?php

declare(strict_types=1);

namespace Cel\Syntax;

use Cel\Span\Span;
use Override;

final readonly class ConditionalExpression extends Expression
{
    public function __construct(
        public Expression $condition,
        public Span $question,
        public Expression $then,
        public Span $color,
        public Expression $else,
    ) {}

    #[Override]
    public function getKind(): ExpressionKind
    {
        return ExpressionKind::Conditional;
    }

    /**
     * @inheritDoc
     */
    #[Override]
    public function getChildren(): array
    {
        return [
            $this->condition,
            $this->then,
            $this->else,
        ];
    }

    /**
     * @inheritDoc
     */
    #[Override]
    public function getSpan(): Span
    {
        return $this->condition->getSpan()->join($this->else->getSpan());
    }
}
