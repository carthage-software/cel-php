<?php

declare(strict_types=1);

namespace Cel\Extension\Math\Function;

use Cel\Extension\Math\Function\Handler\FromBaseFunction\StringIntegerHandler;
use Cel\Function\FunctionInterface;
use Cel\Value\ValueKind;
use Override;

final readonly class FromBaseFunction implements FunctionInterface
{
    #[Override]
    public function getName(): string
    {
        return 'fromBase';
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
        yield [ValueKind::String, ValueKind::Integer] => new StringIntegerHandler();
    }
}
