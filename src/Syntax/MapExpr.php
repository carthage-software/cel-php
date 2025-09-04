<?php

declare(strict_types=1);

namespace Cel\Syntax;

use Override;
use Psl\Vec;

/**
 * Represents a map creation expression.
 * e.g., `{'key': 'value'}`
 */
final readonly class MapExpr extends AbstractExpr
{
    /**
     * @param list<MapExpr\Entry> $entries The entries of the map.
     */
    public function __construct(
        public array $entries,
    ) {}

    #[Override]
    public function jsonSerialize(): array
    {
        return [
            'map_expr' => [
                'entries' => Vec\map($this->entries, static fn(MapExpr\Entry $entry): array => $entry->jsonSerialize()),
            ],
        ];
    }
}
