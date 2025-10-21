<?php

declare(strict_types=1);

namespace Cel\Extension\DateTime\Function;

use Cel\Extension\DateTime\Function\Handler\GetMonthFunction\TimestampHandler;
use Cel\Extension\DateTime\Function\Handler\GetMonthFunction\TimestampWithTimezoneHandler;
use Cel\Function\FunctionInterface;
use Cel\Value\ValueKind;
use Override;

final readonly class GetMonthFunction implements FunctionInterface
{
    #[Override]
    public function getName(): string
    {
        return 'getMonth';
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
