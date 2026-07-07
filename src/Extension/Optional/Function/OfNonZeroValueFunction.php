<?php

declare(strict_types=1);

namespace Cel\Extension\Optional\Function;

use Cel\Extension\Optional\Function\Handler\OfNonZeroValueHandler;
use Cel\Function\FunctionInterface;
use Cel\Function\FunctionOverloadHandlerInterface;
use Cel\Value\ValueKind;
use Override;

/**
 * The `optional.ofNonZeroValue(T) -> optional(T)` global function.
 *
 * @internal
 */
final readonly class OfNonZeroValueFunction implements FunctionInterface
{
    /**
     * @return non-empty-string
     */
    #[Override]
    public function getName(): string
    {
        return 'optional.ofNonZeroValue';
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
        $handler = new OfNonZeroValueHandler();
        foreach (ValueKind::cases() as $kind) {
            yield [$kind] => $handler;
        }
    }
}
