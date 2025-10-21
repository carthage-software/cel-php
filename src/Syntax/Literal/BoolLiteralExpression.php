<?php

declare(strict_types=1);

namespace Cel\Syntax\Literal;

use Cel\Span\Span;
use Cel\Syntax\ExpressionKind;
use Override;

/**
 * @extends LiteralExpression<bool>
 */
final readonly class BoolLiteralExpression extends LiteralExpression
{
    public function __construct(
        public bool $value,
        public string $raw,
        public Span $span,
    ) {}

    #[Override]
    public function getKind(): ExpressionKind
    {
        return ExpressionKind::BoolLiteral;
    }

    #[Override]
    public function getValue(): bool
    {
        return $this->value;
    }

    #[Override]
    public function getRaw(): string
    {
        return $this->raw;
    }

    #[Override]
    public function getChildren(): array
    {
        return [];
    }

    #[Override]
    public function getSpan(): Span
    {
        return $this->span;
    }
}
