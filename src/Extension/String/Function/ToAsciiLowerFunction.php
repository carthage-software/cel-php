<?php

declare(strict_types=1);

namespace Cel\Extension\String\Function;

use Cel\Extension\String\Function\Handler\ToAsciiLower\BytesHandler;
use Cel\Extension\String\Function\Handler\ToAsciiLower\StringHandler;
use Cel\Function\FunctionInterface;
use Cel\Function\FunctionOverloadHandlerInterface;
use Cel\Value\ValueKind;
use Override;

final readonly class ToAsciiLowerFunction implements FunctionInterface
{
    #[Override]
    public function getName(): string
    {
        return 'toAsciiLower';
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
