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

final readonly class FromBaseFunction implements FunctionInterface
{
    #[Override]
    public function getName(): string
    {
        return 'fromBase';
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
        yield [ValueKind::String, ValueKind::Integer] =>
            /**
             * @param CallExpression $call      The call expression representing the function call.
             * @param list<Value>    $arguments The arguments passed to the function.
             */
            static function (CallExpression $call, array $arguments): IntegerValue {
                /** @var StringValue $number */
                $number = $arguments[0];
                /** @var IntegerValue $fromBase */
                $fromBase = $arguments[1];

                if ($number->value === '') {
                    throw new RuntimeException(Str\format('fromBase: cannot convert empty string'), $call->getSpan());
                }

                if ($fromBase->value > 36 || $fromBase->value < 2) {
                    throw new RuntimeException(
                        Str\format('fromBase: base %d is not in the range 2-36', $fromBase->value),
                        $call->getSpan(),
                    );
                }

                return new IntegerValue(Math\from_base($number->value, $fromBase->value));
            };
    }
}
