<?php

declare(strict_types=1);

namespace Cel\Extension\Core\Function;

use Cel\Extension\Core\Function\Handler\UInt\FromBooleanHandler;
use Cel\Extension\Core\Function\Handler\UInt\FromBytesHandler;
use Cel\Extension\Core\Function\Handler\UInt\FromFloatHandler;
use Cel\Extension\Core\Function\Handler\UInt\FromIntegerHandler;
use Cel\Extension\Core\Function\Handler\UInt\FromStringHandler;
use Cel\Extension\Core\Function\Handler\UInt\FromUnsignedIntegerHandler;
use Cel\Function\FunctionInterface;
use Cel\Function\FunctionOverloadHandlerInterface;
use Cel\Value\ValueKind;
use Override;

final readonly class UIntFunction implements FunctionInterface
{
    /**
     * @return non-empty-string
     */
    #[Override]
    public function getName(): string
    {
        return 'uint';
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
        yield [ValueKind::UnsignedInteger] => new FromUnsignedIntegerHandler();
        yield [ValueKind::Integer] => new FromIntegerHandler();
        yield [ValueKind::Float] => new FromFloatHandler();
        yield [ValueKind::Boolean] => new FromBooleanHandler();
        yield [ValueKind::String] => new FromStringHandler();
        yield [ValueKind::Bytes] => new FromBytesHandler();
    }
}
