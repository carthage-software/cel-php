<?php

declare(strict_types=1);

namespace Cel\Syntax\ConstantLiteral;

use Override;

/**
 * Represents an unsigned integer literal.
 */
final readonly class UintConstantLiteral extends AbstractConstantLiteral
{
    /**
     * @param int $value The unsigned integer value.
     */
    public function __construct(
        public int $value,
    ) {}

    #[Override]
    public function jsonSerialize(): array
    {
        return [
            'uint64_value' => $this->value,
        ];
    }
}
