<?php

declare(strict_types=1);

namespace Cel\Runtime\Extension\Core;

use Cel\Runtime\Extension\ExtensionInterface;
use Override;

final readonly class CoreExtension implements ExtensionInterface
{
    /**
     * @inheritDoc
     */
    #[Override]
    public function getFunctions(): array
    {
        return [
            new Function\IntFunction(),
            new Function\StringFunction(),
            new Function\UIntFunction(),
            new Function\FloatFunction(),
            new Function\BoolFunction(),
            new Function\SizeFunction(),
            new Function\BytesFunction(),
            new Function\TypeOfFunction(),
        ];
    }
}
