<?php

declare(strict_types=1);

namespace Cel\Extension\String\Function;

use Cel\Extension\String\Function\Handler\TrimRight\BytesBytesHandler;
use Cel\Extension\String\Function\Handler\TrimRight\BytesHandler;
use Cel\Extension\String\Function\Handler\TrimRight\StringHandler;
use Cel\Extension\String\Function\Handler\TrimRight\StringStringHandler;
use Cel\Function\FunctionInterface;
use Cel\Function\FunctionOverloadHandlerInterface;
use Cel\Value\ValueKind;
use Override;

final readonly class TrimRightFunction implements FunctionInterface
{
    #[Override]
    public function getName(): string
    {
        return 'trimRight';
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
