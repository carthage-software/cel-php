<?php

declare(strict_types=1);

namespace Cel\Extension\String\Function;

use Cel\Extension\String\Function\Handler\ToAsciiUpper\BytesHandler;
use Cel\Extension\String\Function\Handler\ToAsciiUpper\StringHandler;
use Cel\Function\FunctionInterface;
use Cel\Function\FunctionOverloadHandlerInterface;
use Cel\Value\ValueKind;
use Override;

final readonly class ToAsciiUpperFunction implements FunctionInterface
{
    #[Override]
    public function getName(): string
    {
        return 'toAsciiUpper';
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

        yield [ValueKind::Bytes] => new BytesHandler();
    }
}
