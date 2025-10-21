<?php

declare(strict_types=1);

namespace Cel\Extension\Math;

use Cel\Extension\ExtensionInterface;
use Override;

final readonly class MathExtension implements ExtensionInterface
{
    /**
     * @inheritDoc
     */
    #[Override]
    public function getFunctions(): array
    {
        return [
            new Function\BaseConvertFunction(),
            new Function\ClampFunction(),
            new Function\FromBaseFunction(),
            new Function\MaxFunction(),
            new Function\MeanFunction(),
            new Function\MedianFunction(),
            new Function\MinFunction(),
            new Function\SumFunction(),
            new Function\ToBaseFunction(),
        ];
    }

    /**
     * @inheritDoc
     */
    #[Override]
    public function getBinaryOperatorOverloads(): array
    {
        return [];
    }

    /**
     * @inheritDoc
     */
    #[Override]
    public function getUnaryOperatorOverloads(): array
    {
        return [];
    }

    /**
     * @inheritDoc
     */
    #[Override]
    public function getMessageTypes(): array
    {
        return [];
    }

    /**
     * @inheritDoc
     */
    #[Override]
    public function getValueResolvers(): array
    {
        return [];
    }
}
