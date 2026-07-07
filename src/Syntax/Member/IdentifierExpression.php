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
 *
 * A leading dot (`$leadingDot`) marks an absolute reference (`.y`), resolved
 * from the root namespace rather than relative to the current scope.
 *
 * @api
 */
final readonly class IdentifierExpression extends Expression
{
    public function __construct(
        public null|Span $leadingDot,
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
        return null === $this->leadingDot
            ? $this->identifier->getSpan()
            : $this->leadingDot->join($this->identifier->getSpan());
    }
}
