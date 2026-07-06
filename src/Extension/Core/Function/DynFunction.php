<?php

declare(strict_types=1);

namespace Cel\Extension\Core\Function;

use Cel\Extension\Core\Function\Handler\Dyn\DynHandler;
use Cel\Function\FunctionInterface;
use Cel\Function\FunctionOverloadHandlerInterface;
use Cel\Value\ValueKind;
use Override;

/**
 * The `dyn` function returns its argument unchanged.
 *
 * It is a type-system hint (marking a value as dynamically typed) with no
 * runtime effect, provided here for parity with the CEL standard environment.
 */
final readonly class DynFunction implements FunctionInterface
{
    /**
     * @return non-empty-string
     */
    #[Override]
    public function getName(): string
    {
        return 'dyn';
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
        $handler = new DynHandler();
        foreach (ValueKind::cases() as $kind) {
            yield [$kind] => $handler;
        }
    }
}
