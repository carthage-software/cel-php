<?php

declare(strict_types=1);

namespace Cel\Extension\Core\Function;

use Cel\Extension\Core\Function\Handler\Int\FromBooleanHandler;
use Cel\Extension\Core\Function\Handler\Int\FromBytesHandler;
use Cel\Extension\Core\Function\Handler\Int\FromFloatHandler;
use Cel\Extension\Core\Function\Handler\Int\FromIntegerHandler;
use Cel\Extension\Core\Function\Handler\Int\FromStringHandler;
use Cel\Extension\Core\Function\Handler\Int\FromUnsignedIntegerHandler;
use Cel\Function\FunctionInterface;
use Cel\Function\FunctionOverloadHandlerInterface;
use Cel\Value\ValueKind;
use Override;

final readonly class IntFunction implements FunctionInterface
{
    /**
     * @return non-empty-string
     */
    #[Override]
    public function getName(): string
    {
        return 'int';
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
        yield [ValueKind::Integer] => new FromIntegerHandler();
        yield [ValueKind::UnsignedInteger] => new FromUnsignedIntegerHandler();
        yield [ValueKind::Float] => new FromFloatHandler();
        yield [ValueKind::Boolean] => new FromBooleanHandler();
        yield [ValueKind::String] => new FromStringHandler();
        yield [ValueKind::Bytes] => new FromBytesHandler();
    }
}
