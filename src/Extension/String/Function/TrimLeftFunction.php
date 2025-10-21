<?php

declare(strict_types=1);

namespace Cel\Extension\String\Function;

use Cel\Extension\String\Function\Handler\TrimLeft\BytesBytesHandler;
use Cel\Extension\String\Function\Handler\TrimLeft\BytesHandler;
use Cel\Extension\String\Function\Handler\TrimLeft\StringHandler;
use Cel\Extension\String\Function\Handler\TrimLeft\StringStringHandler;
use Cel\Function\FunctionInterface;
use Cel\Function\FunctionOverloadHandlerInterface;
use Cel\Value\ValueKind;
use Override;

final readonly class TrimLeftFunction implements FunctionInterface
{
    #[Override]
    public function getName(): string
    {
        return 'trimLeft';
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
        yield [ValueKind::String] => new StringHandler();

        yield [ValueKind::String, ValueKind::String] => new StringStringHandler();

        yield [ValueKind::Bytes] => new BytesHandler();

        yield [ValueKind::Bytes, ValueKind::Bytes] => new BytesBytesHandler();
    }
}
