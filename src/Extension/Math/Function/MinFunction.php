<?php

declare(strict_types=1);

namespace Cel\Extension\Math\Function;

use Cel\Extension\Math\Function\Handler\MinFunction\ListHandler;
use Cel\Function\FunctionInterface;
use Cel\Value\ValueKind;
use Override;

final readonly class MinFunction implements FunctionInterface
{
    #[Override]
    public function getName(): string
    {
        return 'min';
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
