<?php

declare(strict_types=1);

namespace Cel\Extension\Decimal\Function\Handler;

use Cel\Exception\InternalException;
use Cel\Extension\Decimal\DecimalNumber;
use Cel\Function\FunctionOverloadHandlerInterface;
use Cel\Span\Span;
use Cel\Util\ArgumentsUtil;
use Cel\Value\StringValue;
use Cel\Value\Value;
use Decimal\Decimal;
use Override;
use Psl\Str;
use Throwable;

/**
 * Handles decimal(string) -> DecimalNumber
 */
final readonly class FromStringHandler implements FunctionOverloadHandlerInterface
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
        $arg = ArgumentsUtil::get($arguments, 0, StringValue::class);

        try {
            $decimal = new Decimal($arg->value);
        } catch (Throwable $e) {
            throw InternalException::forMessage(
                Str\format('Failed to create decimal from string "%s": %s', $arg->value, $e->getMessage()),
                $e,
            );
        }

        return new DecimalNumber($decimal)->toCelValue();
    }
}
