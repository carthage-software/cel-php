<?php

declare(strict_types=1);

namespace Cel\Syntax;

use Cel\Span\Span;
use Override;

/**
 * Represents an identifier in the source code.
 */
final readonly class IdentifierNode extends Node
{
    public function __construct(
        /** The name of the identifier. */
        public string $name,
        /** The span of the identifier. */
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
