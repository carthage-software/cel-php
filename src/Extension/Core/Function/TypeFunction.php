<?php

declare(strict_types=1);

namespace Cel\Extension\Core\Function;

use Cel\Extension\Core\Function\Handler\Type\TypeHandler;
use Cel\Function\FunctionInterface;
use Cel\Function\FunctionOverloadHandlerInterface;
use Cel\Value\ValueKind;
use Override;

/**
 * The `type` function returns the type of its argument as a first-class type
 * value, so that expressions like `type(1) == int` and `type(type(1))` work.
 */
final readonly class TypeFunction implements FunctionInterface
{
    /**
     * @return non-empty-string
     */
    #[Override]
    public function getName(): string
    {
        return 'type';
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
        $handler = new TypeHandler();
        foreach (ValueKind::cases() as $kind) {
            yield [$kind] => $handler;
        }
    }
}
