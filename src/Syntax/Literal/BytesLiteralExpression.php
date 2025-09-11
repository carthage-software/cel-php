<?php

declare(strict_types=1);

namespace Cel\Syntax\Literal;

use Cel\Span\Span;
use Cel\Syntax\ExpressionKind;
use Override;

/**
 * @extends LiteralExpression<string>
 */
final readonly class BytesLiteralExpression extends LiteralExpression
{
    public function __construct(
        /**
         * The raw byte string value.
         */
        public string $value,
        public string $raw,
        public Span $span,
    ) {}

    #[Override]
    public function getKind(): ExpressionKind
    {
        return ExpressionKind::BytesLiteral;
    }

    #[Override]
    public function getValue(): string
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
