<?php

declare(strict_types=1);

namespace Cel\Syntax\ConstantLiteral;

use Override;

/**
 * Represents a string literal.
 */
final readonly class StringConstantLiteral extends AbstractConstantLiteral
{
    /**
     * @param string $value The string value.
     */
    public function __construct(
        public string $value,
    ) {}

    #[Override]
    public function jsonSerialize(): array
    {
        return [
            'string_value' => $this->value,
        ];
    }
}
