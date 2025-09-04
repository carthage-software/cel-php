<?php

declare(strict_types=1);

namespace Cel\Syntax;

use Override;
use Psl\Vec;

/**
 * Represents a struct creation expression.
 * e.g., `pkg.MyType{field: value}`
 */
final readonly class StructExpr extends AbstractExpr
{
    /**
     * @param string $typeName The name of the struct type.
     * @param list<StructExpr\Entry> $entries  The entries of the struct.
     */
    public function __construct(
        public string $typeName,
        public array $entries,
    ) {}

    #[Override]
    public function jsonSerialize(): array
    {
        return [
            'struct_expr' => [
                'message_name' => $this->typeName,
                'entries' => Vec\map(
                    $this->entries,
                    static fn(StructExpr\Entry $entry): array => $entry->jsonSerialize(),
                ),
            ],
        ];
    }
}
