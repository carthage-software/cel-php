<?php

declare(strict_types=1);

namespace Cel\Extension\DateTime\Function;

use Cel\Extension\DateTime\Function\Handler\GetDayOfMonthFunction\TimestampHandler;
use Cel\Function\FunctionInterface;
use Cel\Value\ValueKind;
use Override;

/**
 * @internal
 */
final readonly class GetDayOfMonthFunction implements FunctionInterface
{
    #[Override]
    public function getName(): string
    {
        return 'getDayOfMonth';
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
        yield [ValueKind::Timestamp, ValueKind::String] => new TimestampHandler();
    }
}
