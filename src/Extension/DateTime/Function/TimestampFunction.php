<?php

declare(strict_types=1);

namespace Cel\Extension\DateTime\Function;

use Cel\Extension\DateTime\Function\Handler\TimestampFunction\FromFloatHandler;
use Cel\Extension\DateTime\Function\Handler\TimestampFunction\FromIntegerHandler;
use Cel\Extension\DateTime\Function\Handler\TimestampFunction\FromStringHandler;
use Cel\Function\FunctionInterface;
use Cel\Value\ValueKind;
use Override;

final readonly class TimestampFunction implements FunctionInterface
{
    #[Override]
    public function getName(): string
    {
        return 'timestamp';
    }

    #[Override]
    public function isIdempotent(): bool
    {
        return true;
    }

    #[Override]
    public function getOverloads(): iterable
    {
        yield [ValueKind::Integer] => new FromIntegerHandler();
        yield [ValueKind::Float] => new FromFloatHandler();
        yield [ValueKind::String] => new FromStringHandler();
    }
}
