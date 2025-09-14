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
 */
final readonly class MemberAccessExpression extends Expression
{
    public function __construct(
        /** The expression being accessed. */
        public Expression $operand,
        /** The span of the dot `.`. */
        public Span $dot,
        /** The field being accessed. */
        public SelectorNode $field,
    ) {}

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
