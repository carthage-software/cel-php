<?php

declare(strict_types=1);

namespace Cel\Extension\String\Function;

use Cel\Extension\String\Function\Handler\Replace\BytesBytesBytesHandler;
use Cel\Extension\String\Function\Handler\Replace\StringStringStringHandler;
use Cel\Function\FunctionInterface;
use Cel\Function\FunctionOverloadHandlerInterface;
use Cel\Value\ValueKind;
use Override;

final readonly class ReplaceFunction implements FunctionInterface
{
    #[Override]
    public function getName(): string
    {
        return 'replace';
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
        yield [ValueKind::String, ValueKind::String, ValueKind::String] => new StringStringStringHandler();

        yield [ValueKind::Bytes, ValueKind::Bytes, ValueKind::Bytes] => new BytesBytesBytesHandler();
    }
}
