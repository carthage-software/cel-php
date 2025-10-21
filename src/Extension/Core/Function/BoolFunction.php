<?php

declare(strict_types=1);

namespace Cel\Extension\Core\Function;

use Cel\Extension\Core\Function\Handler\Bool\FromBooleanHandler;
use Cel\Extension\Core\Function\Handler\Bool\FromBytesHandler;
use Cel\Extension\Core\Function\Handler\Bool\FromFloatHandler;
use Cel\Extension\Core\Function\Handler\Bool\FromIntegerHandler;
use Cel\Extension\Core\Function\Handler\Bool\FromStringHandler;
use Cel\Extension\Core\Function\Handler\Bool\FromUnsignedIntegerHandler;
use Cel\Function\FunctionInterface;
use Cel\Value\ValueKind;
use Override;

final readonly class BoolFunction implements FunctionInterface
{
    /**
     * @return non-empty-string
     */
    #[Override]
    public function getName(): string
    {
        return 'bool';
    }

    /**
     * @inheritDoc
     */
    #[Override]
    public function isIdempotent(): bool
    {
        return true;
    }

    #[Override]
    public function getOverloads(): iterable
    {
        yield [ValueKind::Boolean] => new FromBooleanHandler();
        yield [ValueKind::Integer] => new FromIntegerHandler();
        yield [ValueKind::UnsignedInteger] => new FromUnsignedIntegerHandler();
        yield [ValueKind::Float] => new FromFloatHandler();
        yield [ValueKind::String] => new FromStringHandler();
        yield [ValueKind::Bytes] => new FromBytesHandler();
    }
}
