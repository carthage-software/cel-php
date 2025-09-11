<?php

declare(strict_types=1);

namespace Cel\Syntax;

use Cel\Span\Span;
use Override;

/**
 * Represents a selector in the source code.
 */
final readonly class SelectorNode extends Node
{
    public function __construct(
        /** The name of the selector. */
        public string $name,
        /** The span of the selector. */
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
