<?php

declare(strict_types=1);

namespace Cel\Syntax\ConstantLiteral;

use Override;

/**
 * Represents an integer literal.
 */
final readonly class IntConstantLiteral extends AbstractConstantLiteral
{
    /**
     * @param int $value The integer value.
     */
    public function __construct(
        public int $value,
    ) {}

    #[Override]
    public function jsonSerialize(): array
    {
        return [
            'int64_value' => $this->value,
        ];
    }
}
