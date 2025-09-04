<?php

declare(strict_types=1);

namespace Cel\Syntax;

use Override;

/**
 * Represents an identifier expression.
 */
final readonly class IdentExpr extends AbstractExpr
{
    /**
     * @param string $name The name of the identifier.
     */
    public function __construct(
        public string $name,
    ) {}

    #[Override]
    public function jsonSerialize(): array
    {
        return [
            'ident_expr' => [
                'name' => $this->name,
            ],
        ];
    }
}
