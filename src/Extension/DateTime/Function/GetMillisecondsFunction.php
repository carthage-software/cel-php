<?php

declare(strict_types=1);

namespace Cel\Extension\DateTime\Function;

use Cel\Extension\DateTime\Function\Handler\GetMillisecondsFunction\DurationHandler;
use Cel\Extension\DateTime\Function\Handler\GetMillisecondsFunction\TimestampHandler;
use Cel\Extension\DateTime\Function\Handler\GetMillisecondsFunction\TimestampWithTimezoneHandler;
use Cel\Function\FunctionInterface;
use Cel\Value\ValueKind;
use Override;

final readonly class GetMillisecondsFunction implements FunctionInterface
{
    #[Override]
    public function getName(): string
    {
        return 'getMilliseconds';
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
