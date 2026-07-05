<?php

declare(strict_types=1);

namespace Cel\Extension\Optional\BinaryOperator;

use Cel\Extension\Optional\BinaryOperator\Handler\EqualityOperator\OptionalOptionalHandler;
use Cel\Operator\BinaryOperatorOverloadInterface;
use Cel\Syntax\Binary\BinaryOperatorKind;
use Cel\Value\ValueKind;
use Override;

/**
 * Provides equality (`==`) and inequality (`!=`) overloads for optional values.
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
        $isEqual = $this->operator === BinaryOperatorKind::Equal;

        yield [ValueKind::Optional, ValueKind::Optional] => new OptionalOptionalHandler($isEqual);
    }
}
