<?php

declare(strict_types=1);

namespace Cel\Extension\List\Function;

use Cel\Extension\List\Function\Handler\ContainsFunction\ContainsHandler;
use Cel\Function\FunctionInterface;
use Cel\Value\ValueKind;
use Override;

final readonly class ContainsFunction implements FunctionInterface
{
    #[Override]
    public function getName(): string
    {
        return 'contains';
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
        $handler = new ContainsHandler();

        // Dynamically generate an overload for each possible type in the list.
        foreach (ValueKind::cases() as $kind) {
            yield [ValueKind::List, $kind] => $handler;
        }
    }
}
