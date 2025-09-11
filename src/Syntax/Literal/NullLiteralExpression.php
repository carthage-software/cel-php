<?php

namespace Cel\Syntax\Literal;

use Cel\Span\Span;
use Cel\Syntax\ExpressionKind;
use Override;

/**
 * @extends LiteralExpression<null>
 */
final readonly class NullLiteralExpression extends LiteralExpression
{
    public function __construct(
        public string $raw,
        public Span $span,
    ) {}

    #[Override]
    public function getKind(): ExpressionKind
    {
        return ExpressionKind::NullLiteral;
    }

    /**
     * @inheritDoc
     */
    #[Override]
    public function getValue(): null
    {
        return null;
    }

    #[Override]
    public function getRaw(): string
    {
        return $this->raw;
    }

    /**
     * @inheritDoc
     */
    #[Override]
    public function getChildren(): array
    {
        return [
            // No children for a null literal.
        ];
    }

    /**
     * @inheritDoc
     */
    #[Override]
    public function getSpan(): Span
    {
        return $this->span;
    }
}
