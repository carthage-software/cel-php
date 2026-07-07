<?php

declare(strict_types=1);

namespace Cel\Extension\Optional\Function;

use Cel\Extension\Optional\Function\Handler\UnwrapHandler;
use Cel\Function\FunctionInterface;
use Cel\Function\FunctionOverloadHandlerInterface;
use Cel\Value\ValueKind;
use Override;

/**
 * The `list(optional(T)).unwrapOpt() -> list(T)` member function, the postfix
 * form of `optional.unwrap`.
 *
 * @internal
 */
final readonly class UnwrapOptFunction implements FunctionInterface
{
    /**
     * @return non-empty-string
     */
    #[Override]
    public function getName(): string
    {
        return 'unwrapOpt';
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
        yield [ValueKind::List] => new UnwrapHandler();
    }
}
