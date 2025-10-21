<?php

declare(strict_types=1);

namespace Cel\Extension\Math\Function;

use Cel\Extension\Math\Function\Handler\BaseConvertFunction\StringIntegerIntegerHandler;
use Cel\Function\FunctionInterface;
use Cel\Value\ValueKind;
use Override;

final readonly class BaseConvertFunction implements FunctionInterface
{
    #[Override]
    public function getName(): string
    {
        return 'baseConvert';
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
        yield [ValueKind::String, ValueKind::Integer, ValueKind::Integer] => new StringIntegerIntegerHandler();
    }
}
