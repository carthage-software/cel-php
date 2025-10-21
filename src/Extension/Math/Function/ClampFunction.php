<?php

declare(strict_types=1);

namespace Cel\Extension\Math\Function;

use Cel\Extension\Math\Function\Handler\ClampFunction\FloatHandler;
use Cel\Extension\Math\Function\Handler\ClampFunction\IntegerHandler;
use Cel\Function\FunctionInterface;
use Cel\Value\ValueKind;
use Override;

final readonly class ClampFunction implements FunctionInterface
{
    #[Override]
    public function getName(): string
    {
        return 'clamp';
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
        yield [ValueKind::Integer, ValueKind::Integer, ValueKind::Integer] => new IntegerHandler();

        yield [ValueKind::Float, ValueKind::Float, ValueKind::Float] => new FloatHandler();
    }
}
