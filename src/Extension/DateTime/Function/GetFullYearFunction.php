<?php

declare(strict_types=1);

namespace Cel\Extension\DateTime\Function;

use Cel\Extension\DateTime\Function\Handler\GetFullYearFunction\TimestampHandler;
use Cel\Extension\DateTime\Function\Handler\GetFullYearFunction\TimestampWithTimezoneHandler;
use Cel\Function\FunctionInterface;
use Cel\Value\ValueKind;
use Override;

final readonly class GetFullYearFunction implements FunctionInterface
{
    #[Override]
    public function getName(): string
    {
        return 'getFullYear';
    }

    #[Override]
    public function isIdempotent(): bool
    {
        return true;
    }

    #[Override]
    public function getOverloads(): iterable
    {
        yield [ValueKind::Timestamp] => new TimestampHandler();
        yield [ValueKind::Timestamp, ValueKind::String] => new TimestampWithTimezoneHandler();
    }
}
