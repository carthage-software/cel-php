<?php

declare(strict_types=1);

namespace Cel\Syntax\ConstantLiteral;

use Override;

/**
 * Represents a double literal.
 */
final readonly class DoubleConstantLiteral extends AbstractConstantLiteral
{
    /**
     * @param float $value The double value.
     */
    public function __construct(
        public float $value,
    ) {}

    #[Override]
    public function jsonSerialize(): array
    {
        return [
            'double_value' => $this->value,
        ];
    }
}
