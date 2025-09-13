<?php

declare(strict_types=1);

namespace Cel\Runtime\Extension\Math\Function;

use Cel\Runtime\Exception\RuntimeException;
use Cel\Runtime\Function\FunctionInterface;
use Cel\Runtime\Value\IntegerValue;
use Cel\Runtime\Value\StringValue;
use Cel\Runtime\Value\Value;
use Cel\Runtime\Value\ValueKind;
use Cel\Syntax\Member\CallExpression;
use Override;
use Psl\Math;
use Psl\Str;

final readonly class ToBaseFunction implements FunctionInterface
{
    #[Override]
    public function getName(): string
    {
        return 'toBase';
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
     * @return iterable<list<ValueKind>, (callable(CallExpression, list<Value>): Value)>
     */
    #[Override]
    public function getOverloads(): iterable
    {
        yield [ValueKind::Integer, ValueKind::Integer] =>
            /**
             * @param CallExpression $call      The call expression representing the function call.
             * @param list<Value>    $arguments The arguments passed to the function.
             */
            static function (CallExpression $call, array $arguments): StringValue {
                /** @var IntegerValue $number */
                $number = $arguments[0];
                /** @var IntegerValue $toBase */
                $toBase = $arguments[1];

                if ($number->value < 0) {
                    throw new RuntimeException(
                        Str\format(
                            'toBase: number %d is negative, only non-negative integers are supported',
                            $number->value,
                        ),
                        $call->getSpan(),
                    );
                }

                if ($toBase->value > 36 || $toBase->value < 2) {
                    throw new RuntimeException(
                        Str\format('toBase: base %d is not in the range 2-36', $toBase->value),
                        $call->getSpan(),
                    );
                }

                return new StringValue(Math\to_base($number->value, $toBase->value));
            };
    }
}
