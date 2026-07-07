<?php

declare(strict_types=1);

namespace Cel\Syntax\Aggregate;

use Cel\Span\Span;
use Cel\Syntax\Expression;
use Cel\Syntax\Node;
use Override;

/**
 * Represents a single key-value entry in a map literal, e.g., `"key": 123`.
 *
 * When `$question` is set, the entry is optional (`{?"key": value}`): the value
 * expression must evaluate to an `optional`, and the entry is only included in
 * the resulting map when that optional holds a value.
 *
 * @api
 */
final readonly class MapEntryNode extends Node
{
    public function __construct(
        /** The span of the optional marker `?`, if this is an optional entry. */
        public null|Span $question,
        /** The key of the map entry. */
        public Expression $key,
        /** The span of the colon `:`. */
        public Span $colon,
        /** The value of the map entry. */
        public Expression $value,
    ) {}

    /**
     * Indicates whether this is an optional map entry (`{?key: value}`).
     */
    public function isOptional(): bool
    {
        return null !== $this->question;
    }

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
