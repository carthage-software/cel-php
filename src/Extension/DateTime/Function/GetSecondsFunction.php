<?php

declare(strict_types=1);

namespace Cel\Extension\DateTime\Function;

use Cel\Extension\DateTime\Function\Handler\GetSecondsFunction\DurationHandler;
use Cel\Extension\DateTime\Function\Handler\GetSecondsFunction\TimestampHandler;
use Cel\Extension\DateTime\Function\Handler\GetSecondsFunction\TimestampWithTimezoneHandler;
use Cel\Function\FunctionInterface;
use Cel\Value\ValueKind;
use Override;

final readonly class GetSecondsFunction implements FunctionInterface
{
    #[Override]
    public function getName(): string
    {
        return 'getSeconds';
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
