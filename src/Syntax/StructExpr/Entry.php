<?php

declare(strict_types=1);

namespace Cel\Syntax\StructExpr;

use Cel\Syntax\IdedExpr;
use JsonSerializable;
use Override;

/**
 * Represents a single entry in a struct creation.
 * e.g., `field: value`
 */
final readonly class Entry implements JsonSerializable
{
    /**
     * @param int<0, max> $id        A unique ID for this entry.
     * @param string      $field     The name of the field.
     * @param IdedExpr    $value     The value of the field.
     * @param bool        $isOptional Whether the field is optional.
     */
    public function __construct(
        public int $id,
        public string $field,
        public IdedExpr $value,
        public bool $isOptional,
    ) {}

    #[Override]
    public function jsonSerialize(): array
    {
        return [
            'id' => $this->id,
            'field_key' => $this->field,
            'value' => $this->value->jsonSerialize(),
            'optional_entry' => $this->isOptional,
        ];
    }
}
