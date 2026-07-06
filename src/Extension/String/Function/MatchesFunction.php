<?php

declare(strict_types=1);

namespace Cel\Extension\String\Function;

use Cel\Extension\String\Function\Handler\Matches\StringStringHandler;
use Cel\Function\FunctionInterface;
use Cel\Function\FunctionOverloadHandlerInterface;
use Cel\Value\ValueKind;
use Override;

final readonly class MatchesFunction implements FunctionInterface
{
    #[Override]
    public function getName(): string
    {
        return 'matches';
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
        yield [ValueKind::String, ValueKind::String] => new StringStringHandler();
    }
}
