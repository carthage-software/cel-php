<?php

declare(strict_types=1);

namespace Cel\Extension\Decimal\Function\Handler;

use Cel\Exception\InternalException;
use Cel\Extension\Decimal\DecimalNumber;
use Cel\Function\FunctionOverloadHandlerInterface;
use Cel\Span\Span;
use Cel\Util\ArgumentsUtil;
use Cel\Value\UnsignedIntegerValue;
use Cel\Value\Value;
use Decimal\Decimal;
use Override;
use Psl\Str;
use Throwable;

/**
 * Handles decimal(uint) -> DecimalNumber
 */
final readonly class FromUnsignedIntegerHandler implements FunctionOverloadHandlerInterface
{
    /**
     * @param Span $span The call expression.
     * @param list<Value> $arguments The function arguments.
     *
     * @return Value The resulting DecimalNumber value.
     *
     * @throws InternalException If the Decimal creation fails.
     */
    #[Override]
    public function __invoke(Span $span, array $arguments): Value
    {
        $arg = ArgumentsUtil::get($arguments, 0, UnsignedIntegerValue::class);

        try {
            $decimal = new Decimal((string) $arg->value);
        } catch (Throwable $e) {
            throw InternalException::forMessage(
                Str\format('Failed to create decimal from unsigned integer: %s', $e->getMessage()),
                $e,
            );
        }

        return new DecimalNumber($decimal)->toCelValue();
    }
}
