<?php

declare(strict_types=1);

namespace Cel\Syntax\Aggregate;

use Cel\Span\Span;
use Cel\Syntax\Expression;
use Cel\Syntax\Node;
use Override;

/**
 * Represents a single key-value entry in a map literal, e.g., `"key": 123`.
 */
final readonly class MapEntryNode extends Node
{
    public function __construct(
        /** The key of the map entry. */
        public Expression $key,
        /** The span of the colon `:`. */
        public Span $colon,
        /** The value of the map entry. */
        public Expression $value,
    ) {}

    #[Override]
    public function getChildren(): array
    {
        return [$this->key, $this->value];
    }

    #[Override]
    public function getSpan(): Span
    {
        return $this->key->getSpan()->join($this->value->getSpan());
    }
}
