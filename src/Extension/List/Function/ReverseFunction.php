<?php

declare(strict_types=1);

namespace Cel\Extension\List\Function;

use Cel\Extension\List\Function\Handler\ReverseFunction\ReverseHandler;
use Cel\Function\FunctionInterface;
use Cel\Value\ValueKind;
use Override;

final readonly class ReverseFunction implements FunctionInterface
{
    #[Override]
    public function getName(): string
    {
        return 'reverse';
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
        yield [ValueKind::List] => new ReverseHandler();
    }
}
