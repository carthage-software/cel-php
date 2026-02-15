<?php

declare(strict_types=1);

namespace Cel\Extension\Decimal\Function\Handler;

use Cel\Exception\InternalException;
use Cel\Extension\Decimal\DecimalNumber;
use Cel\Function\FunctionOverloadHandlerInterface;
use Cel\Span\Span;
use Cel\Util\ArgumentsUtil;
use Cel\Value\IntegerValue;
use Cel\Value\StringValue;
use Cel\Value\Value;
use Decimal\Decimal;
use Override;
use Psl\Str;
use Throwable;

use function restore_error_handler;
use function set_error_handler;

/**
 * Handles decimal(string, int) -> DecimalNumber (with precision)
 */
final readonly class FromStringWithPrecisionHandler implements FunctionOverloadHandlerInterface
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
        $valueArg = ArgumentsUtil::get($arguments, 0, StringValue::class);
        $precisionArg = ArgumentsUtil::get($arguments, 1, IntegerValue::class);

        try {
            // Note: ext-decimal may emit a warning about data loss when applying precision.
            // We suppress this as it's expected behavior when rounding occurs.
            set_error_handler(static fn(): true => true);
            $decimal = new Decimal($valueArg->value, $precisionArg->value);
            restore_error_handler();
        } catch (Throwable $e) {
            restore_error_handler();
            throw InternalException::forMessage(
                Str\format(
                    'Failed to create decimal from string "%s" with precision %d: %s',
                    $valueArg->value,
                    $precisionArg->value,
                    $e->getMessage(),
                ),
                $e,
            );
        }

        return new DecimalNumber($decimal)->toCelValue();
    }
}
