<?php

declare(strict_types=1);

namespace Cel\Extension\Math\Function;

use Cel\Extension\Math\Function\Handler\MedianFunction\ListHandler;
use Cel\Function\FunctionInterface;
use Cel\Value\ValueKind;
use Override;

/**
 * @internal
 */
final readonly class MedianFunction implements FunctionInterface
{
    #[Override]
    public function getName(): string
    {
        return 'median';
    }

    /**
     * @inheritDoc
     */
    #[Override]
    public function isIdempotent(): bool
    {
        return true;
    }

    #[Override]
    public function getOverloads(): iterable
    {
        yield [ValueKind::List] => new ListHandler();
    }
}
