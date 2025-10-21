<?php

declare(strict_types=1);

namespace Cel\Extension\String\Function;

use Cel\Extension\String\Function\Handler\EndsWith\BytesBytesHandler;
use Cel\Extension\String\Function\Handler\EndsWith\StringStringHandler;
use Cel\Function\FunctionInterface;
use Cel\Function\FunctionOverloadHandlerInterface;
use Cel\Value\ValueKind;
use Override;

final readonly class EndsWithFunction implements FunctionInterface
{
    #[Override]
    public function getName(): string
    {
        return 'endsWith';
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

        yield [ValueKind::Bytes, ValueKind::Bytes] => new BytesBytesHandler();
    }
}
