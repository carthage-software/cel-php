<?php

declare(strict_types=1);

namespace Cel\Syntax\ConstantLiteral;

use Override;

/**
 * Represents a null literal.
 */
final readonly class NullConstantLiteral extends AbstractConstantLiteral
{
    #[Override]
    public function jsonSerialize(): array
    {
        return [
            'null_value' => null,
        ];
    }
}
