<?php

declare(strict_types=1);

namespace Cel\Extension\Math\Function;

use Cel\Extension\Math\Function\Handler\MaxFunction\ListHandler;
use Cel\Function\FunctionInterface;
use Cel\Value\ValueKind;
use Override;

final readonly class MaxFunction implements FunctionInterface
{
    #[Override]
    public function getName(): string
    {
        return 'max';
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
        yield [ValueKind::List] => new ListHandler();
    }
}
