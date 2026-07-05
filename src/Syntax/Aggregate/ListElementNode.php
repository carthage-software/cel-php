<?php

declare(strict_types=1);

namespace Cel\Syntax\Aggregate;

use Cel\Span\Span;
use Cel\Syntax\Expression;
use Cel\Syntax\Node;
use Override;

/**
 * Represents a single element in a list literal, e.g., `1` or `?maybe`.
 *
 * When `$question` is set, the element is optional (`[?value]`): the value
 * expression must evaluate to an `optional`, and the element is only appended to
 * the resulting list when that optional holds a value.
 */
final readonly class ListElementNode extends Node
{
    public function __construct(
        /** The span of the optional marker `?`, if this is an optional element. */
        public null|Span $question,
        /** The value of the list element. */
        public Expression $value,
    ) {}

    /**
     * Indicates whether this is an optional list element (`[?value]`).
     */
    public function isOptional(): bool
    {
        return null !== $this->question;
    }

    #[Override]
    public function getChildren(): array
    {
        return [$this->value];
    }

    #[Override]
    public function getSpan(): Span
    {
        if (null !== $this->question) {
            return $this->question->join($this->value->getSpan());
        }

        return $this->value->getSpan();
    }
}
