<?php

declare(strict_types=1);

namespace Cel\Syntax\Binary;

use Cel\Span\Span;
use Cel\Syntax\Expression;
use Cel\Syntax\ExpressionKind;
use Override;

final readonly class BinaryExpression extends Expression
{
    public function __construct(
        public Expression $left,
        public BinaryOperator $operator,
        public Expression $right,
    ) {}

    #[Override]
    public function getKind(): ExpressionKind
    {
        return ExpressionKind::Binary;
    }

    #[Override]
    public function getChildren(): array
    {
        return [
            $this->left,
            $this->operator,
            $this->right,
        ];
    }

    #[Override]
    public function getSpan(): Span
    {
        return $this->left->getSpan()->join($this->right->getSpan());
    }
}
