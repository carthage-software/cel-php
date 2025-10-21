<?php

declare(strict_types=1);

namespace Cel\Extension\Core\Function;

use Cel\Extension\Core\Function\Handler\String\FromBooleanHandler;
use Cel\Extension\Core\Function\Handler\String\FromBytesHandler;
use Cel\Extension\Core\Function\Handler\String\FromDurationHandler;
use Cel\Extension\Core\Function\Handler\String\FromFloatHandler;
use Cel\Extension\Core\Function\Handler\String\FromIntegerHandler;
use Cel\Extension\Core\Function\Handler\String\FromStringHandler;
use Cel\Extension\Core\Function\Handler\String\FromTimestampHandler;
use Cel\Extension\Core\Function\Handler\String\FromUnsignedIntegerHandler;
use Cel\Function\FunctionInterface;
use Cel\Function\FunctionOverloadHandlerInterface;
use Cel\Value\ValueKind;
use Override;

final readonly class StringFunction implements FunctionInterface
{
    /**
     * @return non-empty-string
     */
    #[Override]
    public function getName(): string
    {
        return 'string';
    }

    /**
     * @inheritDoc
     */
    #[Override]
    public function isIdempotent(): bool
    {
        return true;
    }

    /**
     * @return iterable<list<ValueKind>, FunctionOverloadHandlerInterface>
     */
    #[Override]
    public function getOverloads(): iterable
    {
        yield [ValueKind::String] => new FromStringHandler();
        yield [ValueKind::Integer] => new FromIntegerHandler();
        yield [ValueKind::UnsignedInteger] => new FromUnsignedIntegerHandler();
        yield [ValueKind::Float] => new FromFloatHandler();
        yield [ValueKind::Boolean] => new FromBooleanHandler();
        yield [ValueKind::Bytes] => new FromBytesHandler();
        yield [ValueKind::Timestamp] => new FromTimestampHandler();
        yield [ValueKind::Duration] => new FromDurationHandler();
    }
}
