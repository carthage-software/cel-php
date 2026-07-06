<?php

declare(strict_types=1);

namespace Cel\Extension\Core\BinaryOperator;

use Cel\Extension\Core\BinaryOperator\Handler\EqualityOperator\EqualityHandler;
use Cel\Operator\BinaryOperatorOverloadInterface;
use Cel\Syntax\Binary\BinaryOperatorKind;
use Cel\Value\ValueKind;
use Override;

/**
 * Provides total equality (`==`) and inequality (`!=`) over all value types.
 *
 * Equality never errors on a type mismatch: incompatible types are unequal,
 * numeric values compare across `int`/`uint`/`double`, and `null` equals only
 * `null`. The actual comparison is delegated to each value's `isEqual`.
 */
final readonly class EqualityOperator implements BinaryOperatorOverloadInterface
{
    public function __construct(
        private BinaryOperatorKind $operator,
    ) {}

    #[Override]
    public function getOperator(): BinaryOperatorKind
    {
        return $this->operator;
    }

    #[Override]
    public function getOverloads(): iterable
    {
        $handler = new EqualityHandler($this->operator === BinaryOperatorKind::Equal);

        foreach (ValueKind::cases() as $left) {
            foreach (ValueKind::cases() as $right) {
                yield [$left, $right] => $handler;
            }
        }
    }
}
