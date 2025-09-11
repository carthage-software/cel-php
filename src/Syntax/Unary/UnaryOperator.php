<?php

namespace Cel\Syntax\Unary;

use Cel\Span\Span;
use Cel\Syntax\Node;
use Override;

final readonly class UnaryOperator extends Node
{
    public function __construct(
        public UnaryOperatorKind $kind,
        public Span $span,
    ) {}

    #[Override]
    public function getChildren(): array
    {
        return [];
    }

    #[Override]
    public function getSpan(): Span
    {
        return $this->span;
    }
}
