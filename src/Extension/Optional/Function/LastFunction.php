<?php

declare(strict_types=1);

namespace Cel\Extension\Optional\Function;

use Cel\Extension\Optional\Function\Handler\LastHandler;
use Cel\Function\FunctionInterface;
use Cel\Function\FunctionOverloadHandlerInterface;
use Cel\Value\ValueKind;
use Override;

/**
 * The `list(T).last() -> optional(T)` member function.
 *
 * @internal
 */
final readonly class LastFunction implements FunctionInterface
{
    /**
     * @return non-empty-string
     */
    #[Override]
    public function getName(): string
    {
        return 'last';
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
        yield [ValueKind::List] => new LastHandler();
    }
}
