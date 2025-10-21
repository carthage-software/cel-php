<?php

declare(strict_types=1);

namespace Cel\Extension\String\Function;

use Cel\Extension\String\Function\Handler\IndexOf\BytesBytesHandler;
use Cel\Extension\String\Function\Handler\IndexOf\BytesBytesIntegerHandler;
use Cel\Extension\String\Function\Handler\IndexOf\StringStringHandler;
use Cel\Extension\String\Function\Handler\IndexOf\StringStringIntegerHandler;
use Cel\Function\FunctionInterface;
use Cel\Function\FunctionOverloadHandlerInterface;
use Cel\Value\ValueKind;
use Override;

final readonly class IndexOfFunction implements FunctionInterface
{
    #[Override]
    public function getName(): string
    {
        return 'indexOf';
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

        yield [ValueKind::String, ValueKind::String, ValueKind::Integer] => new StringStringIntegerHandler();

        yield [ValueKind::Bytes, ValueKind::Bytes] => new BytesBytesHandler();

        yield [ValueKind::Bytes, ValueKind::Bytes, ValueKind::Integer] => new BytesBytesIntegerHandler();
    }
}
