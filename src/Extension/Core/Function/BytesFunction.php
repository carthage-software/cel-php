<?php

declare(strict_types=1);

namespace Cel\Extension\Core\Function;

use Cel\Extension\Core\Function\Handler\Bytes\FromBytesHandler;
use Cel\Extension\Core\Function\Handler\Bytes\FromStringHandler;
use Cel\Function\FunctionInterface;
use Cel\Value\ValueKind;
use Override;

final readonly class BytesFunction implements FunctionInterface
{
    /**
     * @return non-empty-string
     */
    #[Override]
    public function getName(): string
    {
        return 'bytes';
    }

    /**
     * @inheritDoc
     */
    #[Override]
    public function isIdempotent(): bool
    {
        return true;
    }

    #[Override]
    public function getOverloads(): iterable
    {
        yield [ValueKind::Bytes] => new FromBytesHandler();
        yield [ValueKind::String] => new FromStringHandler();
    }
}
