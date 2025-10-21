<?php

declare(strict_types=1);

namespace Cel\Extension\DateTime\Function;

use Cel\Extension\DateTime\Function\Handler\NowFunction\NowHandler;
use Cel\Function\FunctionInterface;
use Override;

final readonly class NowFunction implements FunctionInterface
{
    #[Override]
    public function getName(): string
    {
        return 'now';
    }

    #[Override]
    public function isIdempotent(): bool
    {
        return false;
    }

    #[Override]
    public function getOverloads(): iterable
    {
        yield [] => new NowHandler();
    }
}
