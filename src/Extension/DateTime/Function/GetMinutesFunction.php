<?php

declare(strict_types=1);

namespace Cel\Extension\DateTime\Function;

use Cel\Extension\DateTime\Function\Handler\GetMinutesFunction\DurationHandler;
use Cel\Extension\DateTime\Function\Handler\GetMinutesFunction\TimestampHandler;
use Cel\Extension\DateTime\Function\Handler\GetMinutesFunction\TimestampWithTimezoneHandler;
use Cel\Function\FunctionInterface;
use Cel\Value\ValueKind;
use Override;

final readonly class GetMinutesFunction implements FunctionInterface
{
    #[Override]
    public function getName(): string
    {
        return 'getMinutes';
    }

    #[Override]
    public function isIdempotent(): bool
    {
        return true;
    }

    #[Override]
    public function getOverloads(): iterable
    {
        yield [ValueKind::Duration] => new DurationHandler();
        yield [ValueKind::Timestamp] => new TimestampHandler();
        yield [ValueKind::Timestamp, ValueKind::String] => new TimestampWithTimezoneHandler();
    }
}
