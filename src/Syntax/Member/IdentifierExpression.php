<?php

declare(strict_types=1);

namespace Cel\Syntax\Member;

use Cel\Span\Span;
use Cel\Syntax\Expression;
use Cel\Syntax\ExpressionKind;
use Cel\Syntax\IdentifierNode;
use Override;

/**
 * Represents an identifier used as an expression, e.g., a variable name.
 */
final readonly class IdentifierExpression extends Expression
{
    public function __construct(
        public IdentifierNode $identifier,
    ) {}

    #[Override]
    public function getKind(): ExpressionKind
    {
        return ExpressionKind::Identifier;
    }

    #[Override]
    public function getChildren(): array
    {
        return [$this->identifier];
    }

    #[Override]
    public function getSpan(): Span
    {
        return $this->identifier->getSpan();
    }
}
