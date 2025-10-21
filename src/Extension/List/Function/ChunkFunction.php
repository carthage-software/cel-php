<?php

declare(strict_types=1);

namespace Cel\Extension\List\Function;

use Cel\Extension\List\Function\Handler\ChunkFunction\ChunkHandler;
use Cel\Function\FunctionInterface;
use Cel\Value\ValueKind;
use Override;

final readonly class ChunkFunction implements FunctionInterface
{
    #[Override]
    public function getName(): string
    {
        return 'chunk';
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
        yield [ValueKind::List, ValueKind::Integer] => new ChunkHandler();
    }
}
