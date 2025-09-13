<?php

declare(strict_types=1);

namespace Cel\Runtime\Extension\Lists;

use Cel\Runtime\Extension\ExtensionInterface;
use Override;

final readonly class ListsExtension implements ExtensionInterface
{
    /**
     * @inheritDoc
     */
    #[Override]
    public function getFunctions(): array
    {
        return [
            new Function\ChunkFunction(),
            new Function\ContainsFunction(),
            new Function\FlattenFunction(),
            new Function\JoinFunction(),
            new Function\ReverseFunction(),
            new Function\SortFunction(),
        ];
    }
}
