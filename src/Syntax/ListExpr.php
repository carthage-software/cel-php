<?php

declare(strict_types=1);

namespace Cel\Syntax;

use Override;
use Psl\Vec;

/**
 * Represents a list creation expression.
 * e.g., `[1, 2, 3]`
 */
final readonly class ListExpr extends AbstractExpr
{
    /**
     * @param list<IdedExpr> $elements The elements of the list.
     */
    public function __construct(
        public array $elements,
    ) {}

    #[Override]
    public function jsonSerialize(): array
    {
        return [
            'list_expr' => [
                'elements' => Vec\map($this->elements, static fn(IdedExpr $expr): array => $expr->jsonSerialize()),
            ],
        ];
    }
}
