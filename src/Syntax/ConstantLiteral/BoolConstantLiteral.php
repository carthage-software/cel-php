<?php

declare(strict_types=1);

namespace Cel\Syntax\ConstantLiteral;

use Override;

/**
 * Represents a boolean literal.
 */
final readonly class BoolConstantLiteral extends AbstractConstantLiteral
{
    /**
     * @param bool $value The boolean value.
     */
    public function __construct(
        public bool $value,
    ) {}

    #[Override]
    public function jsonSerialize(): array
    {
        return [
            'bool_value' => $this->value,
        ];
    }
}
