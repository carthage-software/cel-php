<?php

declare(strict_types=1);

namespace Cel\Runtime\Extension\Math\Function;

use Cel\Runtime\Exception\RuntimeException;
use Cel\Runtime\Function\FunctionInterface;
use Cel\Runtime\Value\IntegerValue;
use Cel\Runtime\Value\ListValue;
use Cel\Runtime\Value\Value;
use Cel\Runtime\Value\ValueKind;
use Cel\Syntax\Member\CallExpression;
use Override;
use Psl\Math;
use Psl\Vec;

final readonly class SumFunction implements FunctionInterface
{
    #[Override]
    public function getName(): string
    {
        return 'sum';
    }

    #[Override]
    public function getOverloads(): iterable
    {
        yield [ValueKind::List] =>
            static function (CallExpression $call, array $arguments): IntegerValue {
                /** @var ListValue $list */
                $list = $arguments[0];
                if ([] === $list->value) {
                    return new IntegerValue(0);
                }

                return new IntegerValue(Math\sum(Vec\map($list->value, static function (Value $v) use ($call): int {
                    if ($v instanceof IntegerValue) {
                        return $v->value;
                    }

                    throw new RuntimeException(
                        'sum() only supports lists of integers, got ' . $v::class,
                        $call->getSpan(),
                    );
                })));
            };
    }
}
