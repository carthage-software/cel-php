<?php

declare(strict_types=1);

namespace Cel\Syntax\Aggregate;

use Cel\Span\Span;
use Cel\Syntax\Expression;
use Cel\Syntax\Node;
use Cel\Syntax\SelectorNode;
use Override;

/**
 * Represents a single field initializer in a message literal, e.g., `field: "value"`.
 *
 * When `$question` is set, the initializer is optional (`Msg{?field: value}`):
 * the value expression must evaluate to an `optional`, and the field is only set
 * when that optional holds a value.
 *
 * @api
 */
final readonly class FieldInitializerNode extends Node
{
    public function __construct(
        /** The span of the optional marker `?`, if this is an optional initializer. */
        public null|Span $question,
        /** The name of the field being initialized. */
        public SelectorNode $field,
        /** The span of the colon `:`. */
        public Span $colon,
        /** The value assigned to the field. */
        public Expression $value,
    ) {}

    /**
     * Indicates whether this is an optional field initializer (`Msg{?field: value}`).
     */
    public function isOptional(): bool
    {
        return null !== $this->question;
    }

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
