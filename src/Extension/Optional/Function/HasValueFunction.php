<?php

declare(strict_types=1);

namespace Cel\Extension\Optional\Function;

use Cel\Extension\Optional\Function\Handler\HasValueHandler;
use Cel\Function\FunctionInterface;
use Cel\Function\FunctionOverloadHandlerInterface;
use Cel\Value\ValueKind;
use Override;

/**
 * The `optional(T).hasValue() -> bool` member function.
 *
 * @internal
 */
final readonly class HasValueFunction implements FunctionInterface
{
    /**
     * @return non-empty-string
     */
    #[Override]
    public function getName(): string
    {
        return 'hasValue';
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
        yield [ValueKind::Optional] => new HasValueHandler();
    }
}
