<?php

declare(strict_types=1);

namespace Cel\Syntax\Member;

use Cel\Span\Span;
use Cel\Syntax\Expression;
use Cel\Syntax\ExpressionKind;
use Override;

/**
 * Represents an index access expression, e.g., `my_list[0]`.
 *
 * When `$question` is set, the access is an optional index (`my_list[?0]`,
 * `my_map[?key]`) that yields an `optional` value rather than erroring when the
 * index or key is absent.
 */
final readonly class IndexExpression extends Expression
{
    public function __construct(
        /** The expression being indexed (e.g., the list or map). */
        public Expression $operand,
        /** The span of the opening bracket `[`. */
        public Span $openingBracket,
        /** The span of the optional marker `?`, if this is an optional index. */
        public null|Span $question,
        /** The expression used as the index. */
        public Expression $index,
        /** The span of the closing bracket `]`. */
        public Span $closingBracket,
    ) {}

    /**
     * Indicates whether this is an optional index access (`operand[?index]`).
     */
    public function isOptional(): bool
    {
        return null !== $this->question;
    }

    #[Override]
    public function getKind(): ExpressionKind
    {
        return ExpressionKind::Index;
    }

    #[Override]
    public function getChildren(): array
    {
        return [$this->operand, $this->index];
    }

    #[Override]
    public function getSpan(): Span
    {
        return $this->operand->getSpan()->join($this->closingBracket);
    }
}
