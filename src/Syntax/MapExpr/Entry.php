<?php

declare(strict_types=1);

namespace Cel\Syntax\MapExpr;

use Cel\Syntax\IdedExpr;
use JsonSerializable;
use Override;

/**
 * Represents a single entry in a map creation.
 * e.g., `key: value`
 */
final readonly class Entry implements JsonSerializable
{
    /**
     * @param int<0, max> $id        A unique ID for this entry.
     * @param IdedExpr    $key       The key of the entry.
     * @param IdedExpr    $value     The value of the entry.
     * @param bool        $isOptional Whether the entry is optional.
     */
    public function __construct(
        public int $id,
        public IdedExpr $key,
        public IdedExpr $value,
        public bool $isOptional,
    ) {}

    #[Override]
    public function jsonSerialize(): array
    {
        return [
            'id' => $this->id,
            'key' => $this->key->jsonSerialize(),
            'value' => $this->value->jsonSerialize(),
            'optional_entry' => $this->isOptional,
        ];
    }
}
