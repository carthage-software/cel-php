<?php

declare(strict_types=1);

namespace Cel\Runtime\Extension\String;

use Cel\Runtime\Extension\ExtensionInterface;
use Override;

final readonly class StringExtension implements ExtensionInterface
{
    /**
     * @inheritDoc
     */
    #[Override]
    public function getFunctions(): array
    {
        return [
            new Function\ContainsFunction(),
            new Function\EndsWithFunction(),
            new Function\IndexOfFunction(),
            new Function\LastIndexOfFunction(),
            new Function\ReplaceFunction(),
            new Function\SplitFunction(),
            new Function\StartsWithFunction(),
            new Function\ToAsciiLowerFunction(),
            new Function\ToAsciiUpperFunction(),
            new Function\ToLowerFunction(),
            new Function\ToUpperFunction(),
            new Function\TrimFunction(),
            new Function\TrimLeftFunction(),
            new Function\TrimRightFunction(),
        ];
    }
}
