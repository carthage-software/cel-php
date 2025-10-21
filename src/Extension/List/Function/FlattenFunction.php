<?php

declare(strict_types=1);

namespace Cel\Extension\List\Function;

use Cel\Extension\List\Function\Handler\FlattenFunction\FlattenHandler;
use Cel\Function\FunctionInterface;
use Cel\Value\ValueKind;
use Override;

final readonly class FlattenFunction implements FunctionInterface
{
    #[Override]
    public function getName(): string
    {
        return 'flatten';
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
        yield [ValueKind::List] => new FlattenHandler();
    }
}
