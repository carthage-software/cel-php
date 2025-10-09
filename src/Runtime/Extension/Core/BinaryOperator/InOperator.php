<?php

declare(strict_types=1);

namespace Cel\Runtime\Extension\Core\BinaryOperator;

use Cel\Runtime\Operator\BinaryOperatorOverloadInterface;
use Cel\Runtime\Value\BooleanValue;
use Cel\Runtime\Value\ListValue;
use Cel\Runtime\Value\Value;
use Cel\Runtime\Value\ValueKind;
use Cel\Syntax\Binary\BinaryOperatorKind;
use Override;
use Psl\Iter;

final readonly class InOperator implements BinaryOperatorOverloadInterface
{
    #[Override]
    public function getOperator(): BinaryOperatorKind
    {
        return BinaryOperatorKind::In;
    }

    #[Override]
    public function getOverloads(): iterable
    {
        $allTypes = [
            ValueKind::Integer,
            ValueKind::UnsignedInteger,
            ValueKind::Float,
            ValueKind::String,
            ValueKind::Bytes,
            ValueKind::Boolean,
            ValueKind::Null,
            ValueKind::List,
            ValueKind::Map,
            ValueKind::Message,
            ValueKind::Timestamp,
            ValueKind::Duration,
        ];

        foreach ($allTypes as $type) {
            yield [$type, ValueKind::List] =>
                /**
                 * @param ListValue $right
                 */
                static fn(Value $left, Value $right): Value => new BooleanValue(Iter\any(
                    $right->value,
                    static fn(Value $item): bool => $item->isEqual($left),
                ));
        }
    }
}
