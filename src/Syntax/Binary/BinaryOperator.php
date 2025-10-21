<?php

declare(strict_types=1);

namespace Cel\Syntax\Binary;

use Cel\Span\Span;
use Cel\Syntax\Node;
use Override;

final readonly class BinaryOperator extends Node
{
    public function __construct(
        public BinaryOperatorKind $kind,
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
