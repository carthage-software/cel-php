<?php

declare(strict_types=1);

namespace Cel\Syntax\Aggregate;

use Cel\Span\Span;
use Cel\Syntax\Expression;
use Cel\Syntax\IdentifierNode;
use Cel\Syntax\Node;
use Cel\Syntax\SelectorNode;
use Override;

/**
 * Represents a single field initializer in a message literal, e.g., `field: "value"`.
 */
final readonly class FieldInitializerNode extends Node
{
    public function __construct(
        /** The name of the field being initialized. */
        public SelectorNode $field,
        /** The span of the colon `:`. */
        public Span $colon,
        /** The value assigned to the field. */
        public Expression $value,
    ) {}

    #[Override]
    public function getChildren(): array
    {
        return [$this->field, $this->value];
    }

    #[Override]
    public function getSpan(): Span
    {
        return $this->field->getSpan()->join($this->value->getSpan());
    }
}
