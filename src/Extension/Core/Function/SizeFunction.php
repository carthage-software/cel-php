<?php

declare(strict_types=1);

namespace Cel\Extension\Core\Function;

use Cel\Extension\Core\Function\Handler\Size\FromBytesHandler;
use Cel\Extension\Core\Function\Handler\Size\FromListHandler;
use Cel\Extension\Core\Function\Handler\Size\FromMapHandler;
use Cel\Extension\Core\Function\Handler\Size\FromStringHandler;
use Cel\Function\FunctionInterface;
use Cel\Function\FunctionOverloadHandlerInterface;
use Cel\Value\ValueKind;
use Override;

final readonly class SizeFunction implements FunctionInterface
{
    /**
     * @return non-empty-string
     */
    #[Override]
    public function getName(): string
    {
        return 'size';
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
        yield [ValueKind::String] => new FromStringHandler();
        yield [ValueKind::Bytes] => new FromBytesHandler();
        yield [ValueKind::List] => new FromListHandler();
        yield [ValueKind::Map] => new FromMapHandler();
    }
}
