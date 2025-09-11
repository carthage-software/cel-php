<?php

declare(strict_types=1);

namespace Cel\Syntax\Unary;

use Cel\Span\Span;
use Cel\Syntax\Expression;
use Cel\Syntax\ExpressionKind;
use Override;

final readonly class UnaryExpression extends Expression
{
    public function __construct(
        public UnaryOperator $operator,
        public Expression $operand,
    ) {}

    #[Override]
    public function getKind(): ExpressionKind
    {
        return ExpressionKind::Unary;
    }

    #[Override]
    public function getChildren(): array
    {
        return [
            $this->operator,
            $this->operand,
        ];
    }

    #[Override]
    public function getSpan(): Span
    {
        return $this->operator->getSpan()->join($this->operand->getSpan());
    }
}
