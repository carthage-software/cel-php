<?php

declare(strict_types=1);

namespace Cel\Extension\List\Function;

use Cel\Extension\List\Function\Handler\JoinFunction\JoinWithoutSeparatorHandler;
use Cel\Extension\List\Function\Handler\JoinFunction\JoinWithSeparatorHandler;
use Cel\Function\FunctionInterface;
use Cel\Value\ValueKind;
use Override;

final readonly class JoinFunction implements FunctionInterface
{
    #[Override]
    public function getName(): string
    {
        return 'join';
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
        yield [ValueKind::List] => new JoinWithoutSeparatorHandler();

        yield [ValueKind::List, ValueKind::String] => new JoinWithSeparatorHandler();
    }
}
