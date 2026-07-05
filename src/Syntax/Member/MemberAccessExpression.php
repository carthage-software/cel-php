<?php

declare(strict_types=1);

namespace Cel\Syntax\Member;

use Cel\Span\Span;
use Cel\Syntax\Expression;
use Cel\Syntax\ExpressionKind;
use Cel\Syntax\SelectorNode;
use Override;

/**
 * Represents a member access expression, e.g., `my_var.field`.
 *
 * When `$question` is set, the access is an optional field selection
 * (`my_var.?field`) that yields an `optional` value rather than erroring
 * when the field is absent.
 */
final readonly class MemberAccessExpression extends Expression
{
    public function __construct(
        /** The expression being accessed. */
        public Expression $operand,
        /** The span of the dot `.`. */
        public Span $dot,
        /** The span of the optional marker `?`, if this is an optional selection. */
        public null|Span $question,
        /** The field being accessed. */
        public SelectorNode $field,
    ) {}

    /**
     * Indicates whether this is an optional field selection (`operand.?field`).
     */
    public function isOptional(): bool
    {
        return null !== $this->question;
    }

    #[Override]
    public function getKind(): ExpressionKind
    {
        return ExpressionKind::MemberAccess;
    }

    #[Override]
    public function getChildren(): array
    {
        return [$this->operand, $this->field];
    }

    #[Override]
    public function getSpan(): Span
    {
        return $this->operand->getSpan()->join($this->field->getSpan());
    }
}
