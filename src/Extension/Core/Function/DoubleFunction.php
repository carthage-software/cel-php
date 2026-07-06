<?php

declare(strict_types=1);

namespace Cel\Extension\Core\Function;

use Cel\Extension\Core\Function\Handler\Float\FromBooleanHandler;
use Cel\Extension\Core\Function\Handler\Float\FromBytesHandler;
use Cel\Extension\Core\Function\Handler\Float\FromFloatHandler;
use Cel\Extension\Core\Function\Handler\Float\FromIntegerHandler;
use Cel\Extension\Core\Function\Handler\Float\FromStringHandler;
use Cel\Extension\Core\Function\Handler\Float\FromUnsignedIntegerHandler;
use Cel\Function\FunctionInterface;
use Cel\Value\ValueKind;
use Override;

/**
 * The `double` conversion function.
 *
 * `double` is CEL's floating-point type; the resulting value is represented
 * internally by {@see \Cel\Value\FloatValue}.
 */
final readonly class DoubleFunction implements FunctionInterface
{
    /**
     * @return non-empty-string
     */
    #[Override]
    public function getName(): string
    {
        return 'double';
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
        yield [ValueKind::Float] => new FromFloatHandler();
        yield [ValueKind::Integer] => new FromIntegerHandler();
        yield [ValueKind::UnsignedInteger] => new FromUnsignedIntegerHandler();
        yield [ValueKind::Boolean] => new FromBooleanHandler();
        yield [ValueKind::String] => new FromStringHandler();
        yield [ValueKind::Bytes] => new FromBytesHandler();
    }
}
