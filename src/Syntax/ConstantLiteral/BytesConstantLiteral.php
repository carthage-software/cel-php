<?php

declare(strict_types=1);

namespace Cel\Syntax\ConstantLiteral;

use Override;

/**
 * Represents a bytes literal.
 */
final readonly class BytesConstantLiteral extends AbstractConstantLiteral
{
    /**
     * @param string $value The bytes value, base64 encoded.
     */
    public function __construct(
        public string $value,
    ) {}

    #[Override]
    public function jsonSerialize(): array
    {
        return [
            'bytes_value' => $this->value,
        ];
    }
}
