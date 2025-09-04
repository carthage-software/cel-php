<?php

declare(strict_types=1);

namespace Cel\Syntax;

use Cel\Span\Span;
use Override;

/**
 * Represents the top-level container for a parsed CEL expression.
 */
final readonly class Expression extends AbstractNode
{
    #[Override]
    public function getSpan(): Span
    {
        throw new \RuntimeException('Not implemented yet.');
    }

    #[Override]
    public function jsonSerialize(): array
    {
        throw new \RuntimeException('Not implemented yet.');
    }
}
