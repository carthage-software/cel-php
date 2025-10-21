<?php

declare(strict_types=1);

namespace Cel\Extension\DateTime\Function;

use Cel\Extension\DateTime\Function\Handler\DurationFunction\FromStringHandler;
use Cel\Function\FunctionInterface;
use Cel\Value\ValueKind;
use Override;

final readonly class DurationFunction implements FunctionInterface
{
    #[Override]
    public function getName(): string
    {
        return 'duration';
    }

    #[Override]
    public function isIdempotent(): bool
    {
        return true;
    }

    #[Override]
    public function getOverloads(): iterable
    {
        yield [ValueKind::String] => new FromStringHandler();
    }
}
