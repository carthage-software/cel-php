<?php

declare(strict_types=1);

namespace Cel\Syntax;

use Cel\Syntax\ConstantLiteral\AbstractConstantLiteral;
use Override;

/**
 * Represents a literal expression.
 */
final readonly class ConstantLiteralExpr extends AbstractExpr
{
    /**
     * @param AbstractConstantLiteral $literal The literal value.
     */
    public function __construct(
        public AbstractConstantLiteral $literal,
    ) {}

    #[Override]
    public function jsonSerialize(): array
    {
        return [
            'const_expr' => $this->literal->jsonSerialize(),
        ];
    }
}
